<?php

namespace Lorry\Presenter\Manage\Moderator;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\EmailFactory;

class Ticket extends AbstractPresenter
{

    public static function getTicket($manager, $id)
    {
        $ticket = $manager->getRepository('Lorry\Model\Ticket')->findOneBy(array('id' => $id));
        if (!$ticket) {
            throw new FileNotFoundException();
        }
        return $ticket;
    }

    public function get($id)
    {
        $this->security->requireModerator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $ticket = self::getTicket($this->manager, $id);
        $this->context['number'] = $ticket->getId();
        $this->context['message'] = $ticket->getMessage();
        $this->context['acknowledged'] = $ticket->isAcknowledged();
        $this->context['escalated'] = $ticket->isEscalated();

        $user = $ticket->getAssociatedUser();
        if ($user) {
            $this->context['user'] = $user;
        }
        $staff = $ticket->getAssignedTo();
        if ($staff) {
            $this->context['staff'] = $staff();
        }

        $this->display('manage/moderator/ticket.twig');
    }

    public function post($id)
    {
        $this->security->requireModerator();
        $this->security->requireIdentification();

        $this->security->requireValidState();

        $ticket = self::getTicket($this->persistence, $id);

        if (!$ticket->isEscalated() && !$ticket->isAcknowledged()) {
            $staff = $this->session->getUser();
            if (isset($_POST['escalate'])) {
                $mail = $this->mail->build('Ticket');

                $mail->setTicketId($ticket->getId());
                $user = $ticket->fetchUser();
                if ($user) {
                    $mail->setReplyTo($user->getEmail());
                    $mail->setUser($user);
                }

                $mail->setMessage($ticket->getMessage());
                $mail->setStaff($staff);

                if ($this->mail->send($mail)) {
                    $ticket->escalate();
                } else {
                    $this->error('ticket',
                        gettext('The ticket could not be escalated.'));
                }
            } elseif (isset($_POST['acknowledge'])) {
                $ticket->acknowledge();
            }
            $ticket->setStaff($staff->getId());
            if ($ticket->modified()) {
                $ticket->save();
            }
        } else {
            if (isset($_POST['return'])) {
                $ticket->dispute();
                if ($ticket->modified()) {
                    $ticket->save();
                }
            }
        }

        $this->get($id);
    }
}
