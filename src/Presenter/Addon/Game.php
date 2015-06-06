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

        //$query = $this->persistence->build('Release')->all();

        /* $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
          $reverse = isset($_GET['reverse']) && $_GET['reverse'] == 1 ? true : false;
          switch($sort) {
          case 'title':
          $query = $query->order('title', $reverse);
          break;
          case 'rating':
          //@TODO sort by rating
          break;
          case 'date':
          default:
          $sort = 'date';
          $query = $query->order('updated', !$reverse);
          break;
          }
          $this->context['sort'] = $sort;
          $this->context['reverse'] = $reverse; */

        $addonRepository = $this->manager->getRepository('Lorry\Model\Addon');
        $this->context['addons'] = $addonRepository->getAllByGame($game);

        $this->display('addon/game.twig');
    }
}
