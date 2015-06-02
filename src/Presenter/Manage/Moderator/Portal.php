<?php

namespace Lorry\Presenter\Manage\Moderator;

use Lorry\Presenter;
use Lorry\Service\LocalisationService;

class Portal extends Presenter
{

    public function get()
    {
        $this->security->requireModerator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $games = $this->manager->getRepository('Lorry\Model\Game');

        $game_objects = $games->findAll();
        $games = array();
        foreach ($game_objects as $game) {
            $games[$game->getId()] = $game->forPresenter();
        }
        $this->context['games'] = $games;

        $addons = array();
        /*foreach ($this->persistence->build('Addon')->bySubmittedForApproval() as $addon) {
            $result = array(
                'addon' =>
                array('id' => $addon->getId(),
                    'title' => $addon->getTitle(),
                    'game' => $addon->getGame()
                ),
                'namespace' => $addon->getProposedShort()
            );
            $owner = $addon->fetchOwner();
            if ($owner) {
                $result['user'] = $owner->forPresenter();
            }
            $addons[] = $result;
        }*/
        $this->context['addons'] = $addons;

        $tickets = array();
        $format = $this->localisation->getFormat(\Lorry\Service\LocalisationService::FORMAT_DATETIME);
        foreach ($this->persistence->build('Ticket')->all()->byNew() as $ticket) {
            $tickets[] = $ticket->forPresenter($format);
        }

        $this->context['tickets'] = $tickets;

        $this->display('manage/moderator/portal.twig');
    }
}
