<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Model\User;
use Lorry\Exception\FileNotFoundException;
use Lorry\Service\LocalisationService;

class Profile extends AbstractPresenter
{

    /**
     *
     * @param string $username
     * @throws FileNotFoundException
     */
    public function get($username)
    {
        /* @var $user \Lorry\Model\User */
        $user = $this->manager->getRepository('Lorry\Model\User')->findOneBy(array('username' => $username));

        if (!$user) {
            throw new FileNotFoundException('unknown user "'.$username.'"');
        }

        if ($user->getUsername() !== $username) {
            $this->redirect($this->config->get('base').'/users/'.$user->getUsername(), true);
            return;
        }

        $this->context['username'] = $user->getUsername();
        $this->context['self'] = $this->session->authenticated() && $user->getId() == $this->session->getUser()->getId();

        $this->context['administrator'] = $user->isAdministrator();
        $this->context['moderator'] = $user->isModerator();

        $flags = array();
        $flags['founder'] = $user->getUsername() === 'B_E';
        $flags['alpha'] = $user->hasFlag(User::FLAG_ALPHA);
        $flags['beta'] = $user->hasFlag(User::FLAG_BETA);
        $flags['vip'] = $user->hasFlag(User::FLAG_VIP);
        $flags['coder'] = $user->hasFlag(User::FLAG_CODER);
        $flags['reporter'] = $user->hasFlag(User::FLAG_REPORTER);
        $this->context['flags'] = $flags;

        if ($user->getRegistration()) {
            $this->context['registration'] = $user->getRegistration()->format($this->localisation->getFormat(LocalisationService::FORMAT_DATE));
        }

        $this->context['profiles'] = array();
        if ($user->getClonkforgeId()) {
            $this->context['clonkforge'] = array(
                'profile' => sprintf(gettext('%s on the Clonk Forge'), $user->getUsername()),
                'url' => sprintf($this->config->get('clonkforge/url'), urlencode($user->getClonkforgeId())));
        }
        if ($user->getGithubName()) {
            $this->context['github'] = array(
                'profile' => sprintf(gettext('%s on GitHub'), $user->getGithubName()),
                'url' => sprintf($this->config->get('github/url'), urlencode($user->getGithubName())));
        }

        $this->context['addons'] = $this->manager->getRepository('Lorry\Model\Addon')->getPublishedByOwner($user);

        $this->display('user/profile.twig');
    }
}
