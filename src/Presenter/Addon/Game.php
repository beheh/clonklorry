<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;
use Lorry\Exception\FileNotFoundException;

class Game extends Presenter
{

    public function get($short)
    {
        /* @var $game \Lorry\Model\Game */
        $game = $this->manager->getRepository('Lorry\Model\Game')->findOneBy(array('short' => $short));
        if (!$game) {
            throw new FileNotFoundException('game '.$short);
        }

        $this->context['title'] = $game->getTitle();
        $this->context['game'] = $game->getTitle();
        $this->context['short'] = $game->getShort();

        $addonRepository = $this->manager->getRepository('Lorry\Model\Addon');
        $this->context['addons'] = $addonRepository->getAllByGame($game);

        $this->display('addon/game.twig');
    }
}
