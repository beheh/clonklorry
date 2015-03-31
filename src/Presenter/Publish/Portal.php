<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Portal extends Presenter
{

    public function get()
    {
        if (!$this->session->authenticated()) {
            $this->display('publish/greeter.twig');
            return;
        }

        $this->security->requireLogin();
        $user = $this->session->getUser();

        $games = $this->persistence->build('Game')->byAnything();

        $this->context['games'] = array();
        foreach ($games as $game) {
            $this->context['games'][] = array(
                'short' => $game->getShort(),
                'title' => $game->getTitle()
            );
        }

        if (isset($_GET['created'])) {
            $this->success('addons', gettext('Addon created.'));
        }

        $addons = $this->persistence->build('Addon')->all()->byOwner($user->getId());

        $this->context['addons'] = array();
        foreach ($addons as $addon) {
            $user_addon = array();
            $user_addon['title'] = $addon->getTitle();
            $user_addon['short'] = $addon->getShort();
            $user_addon['id'] = $addon->getId();
            $game = $addon->fetchGame();
            if ($game) {
                $user_addon['game'] = array('title' => $game->getTitle(), 'short' => $game->getShort());
            }
            $this->context['addons'][] = $user_addon;
        }

        $this->display('publish/portal.twig');
    }
}
