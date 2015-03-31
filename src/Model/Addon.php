<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\Exception\ModelValueInvalidException;

/*
 * @method \Lorry\Model\Addon byId(int $id)
 * @method \Lorry\Model\Addon[] byAnything()
 */

class Addon extends Model
{

    public function getTable()
    {
        return 'addon';
    }

    public function getSchema()
    {
        return array(
            'owner' => 'string',
            'short' => 'string',
            'title_en' => 'string',
            'title_de' => 'string',
            'abbreviation' => 'string',
            'game' => 'int',
            'type' => 'int',
            'introduction' => 'text',
            'description' => 'text',
            'website' => 'url',
            'bugtracker' => 'url',
            'forum' => 'url',
            'proposed_short' => 'string',
            'approval_submit' => 'datetime',
            'approval_comment' => 'text');
    }

    public function setOwner($owner)
    {
        return $this->setValue('owner', $owner);
    }

    /**
     * @return \Lorry\Model\Addon[]
     */
    public function byOwner($owner)
    {
        return $this->byValue('owner', $owner);
    }

    public function getOwner()
    {
        return $this->getValue('owner');
    }

    /**
     * @return User
     */
    public function fetchOwner()
    {
        return $this->fetch('User', 'owner');
    }

    public function validateAddonShort($short)
    {
        $this->validateString($short, 4, 30);
        $this->validateRegexp($short, '/^[a-z0-9]+$/i');
    }

    public function setShort($short)
    {
        $short = trim(strtolower($short));
        if ($short) {
            $this->validateAddonShort($short);
        } else {
            $short = null;
        }
        return $this->setValue('short', $short);
    }

    /**
     * @return \Lorry\Model\Addon
     */
    public function byShort($short, $game = null)
    {
        $constraints = array('short' => $short);
        if ($game !== null) {
            $constraints['game'] = $game;
        }
        return $this->byValues($constraints);
    }

    public function getShort()
    {
        return $this->getValue('short');
    }

    public function setTitle($title, $language = null)
    {
        $field = $this->localizeField('title', $language);

        $title = ucfirst(trim($title));
        if (empty($title)) {
            $title = null;
        }

        if (!($language == 'en' && !empty($this->getTitle('de'))) && !($language
            == 'de' && !empty($this->getTitle('en'))) || $title) {
            $this->validateString($title, 3, 50);
        }
        return $this->setValue($field, $title);
    }

    public function getTitle($language = null)
    {
        if ($language === null) {
            $title_en = $this->getTitle('en');
            $title_de = $this->getTitle('de');
            if (!empty($title_en) && empty($title_de)) {
                return $title_en;
            }
            if (empty($title_en) && !empty($title_de)) {
                return $title_de;
            }
        }
        $title = $this->getValue($this->localizeField('title', $language));
        if ($language === null && empty($title)) {
            return gettext('Unnamed addon');
        }
        return $title;
    }

    /**
     * @return \Lorry\Model\Addon|\Lorry\Model\Addon[]
     */
    public function byTitle($title, $owner = 0, $game = 0, $language = null)
    {
        $constraints = array($this->localizeField('title', $language) => $title);
        if ($owner != 0) {
            $constraints['owner'] = $owner;
        }
        if ($game != 0) {
            $constraints['game'] = $game;
        }
        return $this->byValues($constraints);
    }

    public function setAbbreviation($abbreviation)
    {
        $abbreviation = trim($abbreviation);
        if ($abbreviation) {
            $this->validateString($abbreviation, 2, 6);
        } else {
            $abbreviation = null;
        }
        return $this->setValue('abbreviation', $abbreviation);
    }

    public function getAbbreviation()
    {
        return $this->getValue('abbreviation');
    }

    /**
     * @return \Lorry\Model\Addon|\Lorry\Model\Addon[]
     */
    public function byAbbreviation($abbreviation, $game)
    {
        $constraints = array('abbreviation' => $abbreviation, 'game' => $game);
        return $this->byValues($constraints);
    }

    public function setGame($game)
    {
        return $this->setValue('game', $game);
    }

    /**
     * @return \Lorry\Model\Addon[]
     */
    public function byGame($game)
    {
        return $this->byValue('game', $game);
    }

    public function getGame()
    {
        return $this->getValue('game');
    }

    /**
     * @return Game
     */
    public function fetchGame()
    {
        return $this->fetch('Game', 'game');
    }

    public function setType($type)
    {
        return $this->setValue('type', $type);
    }

    public function getType()
    {
        return $this->getValue('type');
    }

    public function setIntroduction($introduction)
    {
        if ($introduction) {
            $this->validateString($introduction, 50, 200);
        } else {
            $introduction = null;
        }
        return $this->setValue('introduction', $introduction);
    }

    public function getIntroduction()
    {
        return $this->getValue('introduction');
    }

    public function setDescription($description)
    {
        if ($description) {
            $this->validateString($description, 0, 4096);
        } else {
            $description = null;
        }
        return $this->setValue('description', $description);
    }

    public function getDescription()
    {
        return $this->getValue('description');
    }

    public function setWebsite($website)
    {
        if ($website) {
            $this->validateUrl($website);
        } else {
            $website = null;
        }
        return $this->setValue('website', $website);
    }

    public function getWebsite()
    {
        return $this->getValue('website');
    }

    public function setBugtracker($bugtracker)
    {
        if ($bugtracker) {
            $this->validateUrl($bugtracker);
        } else {
            $bugtracker = null;
        }
        return $this->setValue('bugtracker', $bugtracker);
    }

    public function getBugtracker()
    {
        return $this->getValue('bugtracker');
    }

    public function setForum($forum)
    {
        if ($forum) {
            $this->validateUrl($forum);
        } else {
            $forum = null;
        }
        return $this->setValue('forum', $forum);
    }

    public function getForum()
    {
        return $this->getValue('forum');
    }

    public function isApproved()
    {
        return $this->getShort() !== null;
    }

    public function isRejected()
    {
        return $this->getShort() === null && $this->getApprovalSubmit() === null
            && $this->getApprovalComment() !== null;
    }

    public function setProposedShort($proposed_short)
    {
        $proposed_short = trim(strtolower($proposed_short));
        if ($proposed_short) {
            $this->validateAddonShort($proposed_short);
        } else {
            $proposed_short = null;
        }
        return $this->setValue('proposed_short', $proposed_short);
    }

    public function getProposedShort()
    {
        return $this->getValue('proposed_short');
    }

    public function setApprovalSubmit($approval_submit)
    {
        return $this->setValue('approval_submit', $approval_submit);
    }

    public function getApprovalSubmit()
    {
        return $this->getValue('approval_submit');
    }

    /**
     * @return \Lorry\Model\Addon[]
     */
    public function bySubmittedForApproval()
    {
        $constraints = array('short' => null, 'approval_submit' => array('!=', null));
        $this->all()->order('approval_submit');
        return $this->byValues($constraints);
    }

    public function isSubmittedForApproval()
    {
        return $this->getApprovalSubmit() !== null;
    }

    public function submitForApproval()
    {
        if ($this->getProposedShort() === null) {
            throw new ModelValueInvalidException(gettext('required'));
        }
        $this->setApprovalComment(null);
        return $this->setApprovalSubmit(time());
    }

    public function withdrawSubmission()
    {
        return $this->setApprovalSubmit(null);
    }

    public function approve($comment = null)
    {
        if ($comment) {
            $this->validateComment($comment);
        } else {
            $comment = null;
        }
        $this->setApprovalComment($comment);
        return $this->setShort($this->getProposedShort());
    }

    public function reject($comment = null)
    {
        if ($comment) {
            $this->validateComment($comment);
        } else {
            $comment = null;
        }
        $this->setApprovalComment($comment);
        return $this->setApprovalSubmit(null);
    }

    public function validateComment($comment)
    {
        $this->validateString($comment, 0, 1024);
    }

    public function getApprovalComment()
    {
        return $this->getValue('approval_comment');
    }

    public function setApprovalComment($comment)
    {
        return $this->setValue('approval_comment', $comment);
    }

    public function __toString()
    {
        return $this->getTitle().'';
    }

    public function forApi($detailed = false)
    {
        $result = array();

        $result['id'] = $this->getShort();
        $result['title_en'] = $this->getTitle('en');
        $result['title_de'] = $this->getTitle('de');
        if ($this->getAbbreviation()) {
            $result['abbreviation'] = $this->getAbbreviation();
        }
        $result['introduction'] = $this->getIntroduction();
        if ($detailed) {
            $result['description'] = $this->getDescription();
        }

        $dependencies = array();
        foreach ($this->persistence->build('Release')->all()->byRelease($this->getId()) as $dependency) {
            $dependencies[] = $dependency->fetchRequires()->getId();
        }
        $result['dependencies'] = $dependencies;

        return $result;
    }
}
