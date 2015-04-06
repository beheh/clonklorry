<?php

namespace Lorry\Presenter\Publish;

use DateTime;
use Lorry\Presenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ModelValueInvalidException;
use Lorry\Exception\ForbiddenException;

class Release extends Presenter
{

    public static function getRelease($persistence, $id, $version)
    {
        $release = $persistence->build('Release')->byVersion($version, $id);
        if (!$release) {
            throw new FileNotFoundException();
        }
        return $release;
    }

    public function get($id, $version)
    {
        $this->security->requireLogin();

        $addon = Edit::getAddon($this->persistence, $id,
                $this->session->getUser());
        $release = Release::getRelease($this->persistence, $addon->getId(),
                $version);
        $game = $addon->fetchGame();

        if ($addon->isApproved()) {
            $this->context['approved'] = true;
        } elseif ($addon->isSubmittedForApproval()) {
            $this->context['submitted'] = true;
        }
        $this->context['published'] = $release->isReleased();


        $this->context['title'] = sprintf(gettext('Edit %s'),
            $addon->getTitle().' '.$release->getVersion());
        $this->context['game'] = $game->getShort();
        $this->context['addon'] = array('title' => $addon->getTitle(),
            'id' => $addon->getId(),
            'game' => $game->forPresenter());
        $this->context['version'] = $release->getVersion();

        $latest = $this->persistence->build('Release')->latest($addon->getId());
        $this->context['latest'] = ($latest && $latest->getId() == $release->getId());
        $this->context['scheduled'] = $release->isScheduled();

        /* Basic */

        if (!isset($this->context['new_version'])) {
            $this->context['new_version'] = $release->getVersion();
        }
        if (isset($_GET['version-changed'])) {
            $this->success('version', gettext('Release saved.'));
        }

        $this->context['date'] = date('Y-m-d', strtotime('first day of this month')); // for date input fields


        if(!isset($this->context['initial'])) {
            $this->context['initial'] = $release->getInitial();
        }

        /* Files */

        try {
            $this->security->requireUploadRights();
            $this->context['can_upload'] = true;
        } catch (ForbiddenException $ex) {
            $this->warning('files', $ex->getMessage().'.');
        }

        /* Depedencies */

        /* Changes */

        $this->context['whatsnew'] = $release->getWhatsnew();
        $this->context['changelog'] = $release->getChangelog();

        /* Publish */

        $datetime = new DateTime('tomorrow noon');
        $this->context['datetime'] = $datetime->format('Y-m-d\TH:i:s');
        $this->context['shipping'] = $release->isShipping();

        $this->display('publish/release.twig');
    }

    public function post($id, $version)
    {
        $this->security->requireLogin();
        $this->security->requireValidState();

        $addon = Edit::getAddon($this->persistence, $id,
                $this->session->getUser());
        $release = Release::getRelease($this->persistence, $addon->getId(),
                $version);

        /* Basic */

        if (isset($_POST['basic-form'])) {
            if(isset($_POST['release-remove'])) {
                $release->delete();
                $this->redirect('/publish/'.$id.'#releases');
                return;
            }

            $new_version = filter_input(INPUT_POST, 'version');
            $this->context['new_version'] = $new_version;

            $errors = array();

            try {
                $existing = $this->persistence->build('Release')->byVersion($new_version,
                    $id);
                if ($existing && $existing->getId() != $release->getId()) {
                    $errors[] = gettext('Version already exists.');
                }

                $release->setVersion($new_version);
            } catch (ModelValueInvalidException $ex) {
                $errors[] = sprintf(gettext('Version is %s.'), $ex->getMessage());
            }

            $archive_confirm = filter_input(INPUT_POST, 'archive-confirm', FILTER_VALIDATE_BOOLEAN);
            $initial = filter_input(INPUT_POST, 'initial');
            if(!$initial || !$archive_confirm) {
                $initial = null;
            }
            $this->context['initial_confirm'] = $archive_confirm;
            $this->context['initial'] = $initial;
            try {
                $release->setInitial($initial);
            } catch (ModelValueInvalidException $ex) {
                $errors[] = sprintf(gettext('Initial release date is %s.'), $ex->getMessage());
            }

            if (empty($errors)) {
                if ($release->modified()) {
                    $release->save();
                    $this->success('basic', gettext('Release saved.'));
                }
            } else {
                $this->error('basic', implode($errors, '<br>'));
            }
        }

        /* Files */

        /* Depedencies */

        /* Changes */

        if (isset($_POST['changes-form'])) {
            $whatsnew = trim(filter_input(INPUT_POST, 'whatsnew'));
            $changelog = trim(filter_input(INPUT_POST, 'changelog'));

            $errors = array();

            try {
                $release->setWhatsnew($whatsnew);
            } catch (ModelValueInvalidException $ex) {
                $errors[] = sprintf(gettext('&quot;%s&quot; is %s.'),
                    gettext('What\'s new?'), $ex->getMessage());
            }
            try {
                $release->setChangelog($changelog);
            } catch (ModelValueInvalidException $ex) {
                $errors[] = sprintf(gettext('Changelog is %s.'),
                    $ex->getMessage());
            }

            if (empty($errors)) {
                if ($release->modified()) {
                    $release->save();
                    $this->success('changes', gettext('Changes saved.'));
                }
            } else {
                $this->error('changes', implode($errors, '<br>'));
            }
        }

        /* Publish */

        if (isset($_POST['publish-quick-form'])) {
            $confirm = filter_input(INPUT_POST, 'confirm',
                    FILTER_VALIDATE_BOOLEAN) || false;
            if ($confirm) {
                $release->setShipping(true);
                $release->save();
                $this->job->submit('Release', array($release->getId()));
                $this->redirect('/publish/'.$id.'/'.$version.'#release');
            } else {
                $this->error('publish-quick', gettext('Confirmation required.'));
            }
        }

        return $this->get($id, $version);
    }
}
