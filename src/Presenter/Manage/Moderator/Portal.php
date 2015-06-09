<?php

namespace Lorry\Presenter\Manage\Moderator;

use Lorry\Presenter;

class Portal extends Presenter
{

    public function get()
    {
        $this->security->requireModerator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $this->context['games'] = $this->manager->getRepository('Lorry\Model\Game')->findAll();

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
        /*foreach ($this->manager->getRepository('Lorry\Model\Ticket')->getAllNewTickets() as $ticket) {
            $tickets[] = $ticket->forPresenter($format);
        }*/

        $this->context['tickets'] = $tickets;

        $this->display('manage/moderator/portal.twig');
    }
}
