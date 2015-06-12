<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Exception\FileNotFoundException;

class Presentation extends AbstractPresenter
{

    public function get($gamename, $addonname, $version = 'latest')
    {
        $game = $this->persistence->build('Game')->byShort($gamename);
        if (!$game) {
            throw new FileNotFoundException('game '.$gamename);
        }

        $addon = $this->persistence->build('Addon')->byShort($addonname,
            $game->getId());
        if (!$addon) {
            $addon = $this->persistence->build('Addon')->byAbbreviation($addonname,
                $game->getId());
            if ($addon) {
                return $this->redirect('/addons/'.$game->getShort().'/'.$addon->getShort());
            }
            throw new FileNotFoundException('addon '.$addonname);
        }

        if ($version == 'latest') {
            $release = $this->persistence->build('Release')->latest($addon->getId());
        } else {
            $release = $this->persistence->build('Release')->byVersion($version,
                $addon->getId());
        }
        if (!$release || !$release->isScheduled()) {
            throw new FileNotFoundException('release with version '.$version);
        }

        $this->context['title'] = $addon->getTitle();

        $this->context['game'] = array('title' => $game->getTitle(), 'short' => $game->getShort());
        $this->context['addon'] = array('id' => $addon->getId(), 'title' => $addon->getTitle(),
            'short' => $addon->getShort());


        $owner = $this->persistence->build('User')->byId($addon->getOwner());

        if ($owner) {
            $this->context['developer'] = array('name' => $owner->getUsername(),
                'url' => $owner->getProfileUrl());
        }
        $this->context['version'] = $release->getVersion();

        $this->context['description'] = $addon->getDescription();
        $this->context['whatsnew'] = $release->getWhatsnew();
        $this->context['changelog'] = $release->getChangelog();

        $this->context['dependencies'] = array();
        $dependencies = $release->fetchDependencies();
        foreach ($dependencies as $dependency) {
            $dependency_release = $dependency->fetchRelease();
            if (!$dependency_release) {
                continue;
            }
            $dependency_addon = $dependency_release->fetchAddon();
            if (!$dependency_addon) {
                continue;
            }
            $this->context['dependencies'][] = array('title' => $dependency_addon->getTitle(),
                'short' => $dependency_addon->getShort());
        }

        $this->context['requirements'] = array();
        $requirements = $release->fetchRequirements();
        foreach ($requirements as $requirement) {
            $requirement_release = $requirement->fetchRequired();
            if (!$requirement_release) {
                continue;
            }
            $requirement_addon = $requirement_release->fetchAddon();
            if (!$requirement_addon) {
                continue;
            }
            $game = $requirement_addon->fetchGame();

            $this->context['requirements'][] = array(
                'title' => $requirement_addon->getTitle(),
                'short' => $requirement_addon->getShort(),
                'game' => $game->getShort());
        }

        $this->context['releaseday'] = strtr(gettext('%day% of %month% %year%'),
            array(
            '%day%' => $this->localisation->countedNumber('1'),
            '%month%' => $this->localisation->namedMonth('1'),
            '%year%' => '2013'));

        $this->context['website'] = $addon->getWebsite();
        $this->context['bugtracker'] = $addon->getBugtracker();

        $modify = false;
        if ($this->session->authenticated()) {
            $user = $this->session->getUser();
            $modify = $addon->getOwner() == $user->getId() || $user->isAdministrator();
        }
        $this->context['modify'] = $modify;

        $this->display('addon/presentation.twig');
    }
}
