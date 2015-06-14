<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Exception\FileNotFoundException;

class Game extends AbstractPresenter
{

    public function get($namespace)
    {
        /* @var $game \Lorry\Model\Game */
        $game = $this->manager->getRepository('Lorry\Model\Game')->findOneBy(array('namespace' => $namespace));
        if (!$game) {
            throw new FileNotFoundException('game '.$namespace);
        }

        $this->context['title'] = $game->getTitle();
        $this->context['game'] = $game->getTitle();
        $this->context['namespace'] = $game->getNamespace();

        $addonRepository = $this->manager->getRepository('Lorry\Model\Addon');
        $this->context['addons'] = $addonRepository->getAllByGame($game);

        $this->display('addon/game.twig');
    }
}
