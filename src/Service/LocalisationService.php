<?php

namespace Lorry\Service;

use Lorry\Service;
use Lorry\Logger\LoggerFactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LocalisationService extends Service
{
    /**
     *
     * @var \Lorry\Service\SessionService
     */
    protected $session;

    /**
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $manager;

    public function __construct(LoggerFactoryInterface $loggerFactory, ObjectManager $manager, SessionService $session)
    {
        parent::__construct($loggerFactory);
        $this->manager = $manager;
        $this->session = $session;
    }

    /**
     *
     * @return \Lorry\Model\Language[]
     */
    final public function getAvailableLanguages()
    {
        return $this->manager->getRepository('Lorry\Model\Language')->findAll();
    }

    final public function getLocalizedCountries()
    {
        $countries = array();
        foreach ($this->getAvailableLanguages() as $language) {
            $exploded = explode('-', $language);
            $countries[$exploded[0]] = true;
        }
        return array_keys($countries);
    }

    /**
     *
     * @param string $language
     * @return bool
     */
    final public function verifyLanguage($language)
    {
        if (in_array($language, $this->getAvailableLanguages())) {
            return $language;
        }
        return false;
    }

    final public function getLanguage($language) {
        return $this->manager->getRepository('Lorry\Model\Language')->findOneBy(array('key' => $language));
    } 

    /**
     * @var Language
     */
    private $displayLanguage = null;

    /**
     *
     * @return string
     */
    public function getDisplayLanguage()
    {
        if ($this->displayLanguage) {
            return $this->displayLanguage;
        }

        $available = $this->getAvailableLanguages();

        if (!$this->session) {
            return $available[0];
        }

        try {
            if ($this->session->authenticated()) {
                $language = $this->session->getUser()->getLanguage();
                if ($this->setDisplayLanguage($language)) {
                    return $language;
                }
            }
        } catch (\RuntimeException $exception) {
            $this->logger->warning($exception);
        }

        if (isset($_COOKIE['lorry_language'])) {
            $language = $_COOKIE['lorry_language'];
            if ($this->setDisplayLanguage($language)) {
                return $language;
            } else {
                $this->resetDisplayLanguage();
            }
        }

        if (function_exists('http_negotiate_language')) {
            $language = http_negotiate_language($available);
        } else {
            $language = $available[0];
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $accept = str_replace(' ', '', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
                // sort by quality
                $priorities = array();
                foreach ($accept as $part) {
                    $q = 1;
                    if (substr_count($part, ';q=') == 1) {
                        $parts = explode(';q=', $part);
                        $part = $parts[0];
                        $q = floatval($parts[1]);
                    }
                    $priorities[$part] = $q;
                }
                arsort($priorities);
                foreach ($priorities as $current => $val) {
                    $found = false;
                    foreach ($available as $j => $offered) {
                        if (strpos($offered, $current) === 0) {
                            $language = $offered;
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        break;
                    }
                }
            }
        }

        $this->setDisplayLanguage($language);
        return $this->displayLanguage;
    }

    /**
     *
     * @param string $language
     * @return bool
     */
    final public function setDisplayLanguage($language)
    {
        if ($this->verifyLanguage($language)) {
            $this->displayLanguage = $language;
            if (!isset($_COOKIE['lorry_language']) || $_COOKIE['lorry_language'] != $language) {
                setcookie('lorry_language', $language, time() + 60 * 60 * 24 * 365, '/');
            }
            return true;
        }
        return false;
    }

    final public function resetDisplayLanguage()
    {
        setcookie('lorry_language', '', 0, '/');
    }
    private $localized = false;

    /**
     *
     */
    final public function localize()
    {
        if ($this->localized) {
            return false;
        }

        $requested = $this->getDisplayLanguage();
        header('Content-Language: '.$requested);

        $this->localized = true;

        $this->silentLocalize($requested);
    }
    private $current_language = false;

    /**
     *
     * @param string $language
     * @return bool
     */
    final public function silentLocalize($language)
    {
        if ($language == $this->current_language) {
            return true;
        }
        if (!$this->verifyLanguage($language)) {
            return false;
        }

        $language = str_replace('-', '_', $language);
        putenv('LC_ALL='.$language);
        setlocale(LC_ALL, $language.'.UTF-8');

        $textdomain = 'lorry-'.$language;
        bindtextdomain($textdomain, __DIR__.'/../../app/locale');
        bind_textdomain_codeset($textdomain, 'UTF-8');
        textdomain($textdomain);

        $this->current_language = $language;

        return true;
    }

    /**
     *
     * @return bool
     */
    final public function resetLocalize()
    {
        return $this->silentLocalize($this->getDisplayLanguage());
    }
    const FORMAT_DATETIME = 1;
    const FORMAT_DATE = 2;
    const FORMAT_TIME = 3;

    /**
     *
     * @param int $format
     * @return string
     */
    final public function getFormat($format)
    {
        switch ($format) {
            case self::FORMAT_DATETIME:
                return gettext('d-m-Y H:i');
                break;
            case self::FORMAT_DATE:
                return gettext('d-m-Y');
                break;
            case self::FORMAT_TIME:
                return gettext('H:i');
                break;
        }
    }

    /**
     *
     * @param string $language
     * @return string
     */
    final public function namedLanguage($language)
    {
        switch ($language) {
            case 'en-US':
                return gettext('English');
                break;
            case 'de-DE':
                return gettext('German');
                break;
            default:
                return $language;
                break;
        }
    }

    /**
     *
     * @param int $month
     * @return string
     */
    final public function namedMonth($month)
    {
        switch ($month) {
            case 1:
                return gettext('January');
            case 2:
                return gettext('February');
            case 3:
                return gettext('March');
            case 4:
                return gettext('April');
            case 5:
                return gettext('May');
            case 6:
                return gettext('June');
            case 7:
                return gettext('July');
            case 8:
                return gettext('August');
            case 9:
                return gettext('September');
            case 10:
                return gettext('October');
            case 11:
                return gettext('November');
            case 12:
                return gettext('December');
        }
    }

    /**
     *
     * @param int $number
     * @return string
     */
    final public function countedNumber($number)
    {
        switch ($number) {
            case 1:
                return gettext('1st');
            case 2:
                return gettext('2nd');
            case 3:
                return gettext('3rd');
            default:
                return strtr(gettext('%n%th'), array('%n%' => $number));
        }
    }
}
