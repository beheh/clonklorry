<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;
use Lorry\Model\Ticket;
use Lorry\Validator\TicketValidator;
use Lorry\Exception\ValidationException;
use Lorry\Exception\TooManyRequestsException;

class Contact extends Presenter
{
    /**
     * @Inject
     * @var \BehEh\Flaps\Flaps
     */
    private $flaps;

    public function get()
    {
        $user = false;
        if ($this->session->authenticated()) {
            $user = $this->session->getUser();
        }

        $this->context['user'] = $user;

        $this->context['hide_greeter'] = true;
        $this->context['address'] = $this->config->get('contact/address');
        $this->context['legal_address'] = $this->config->get('contact/legal');
        if ($user) {
            $this->context['by'] = $user->getUsername();
        }

        $this->display('site/contact.twig');
    }

    public function post()
    {
        $ticketValidator = new TicketValidator();
        $ticketRepository = $this->manager->getRepository('Lorry\Model\Ticket');

        $user = false;
        if ($this->session->authenticated()) {
            $user = $this->session->getUser();
        }

        $ticket = new Ticket();

        $email = null;
        if ($user) {
            $ticket->setAssociatedUser($user);
            $email = $user->getEmail();
        }
        else {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $this->context['email'] = $email;
        }

        $ticket->setResponseEmail($email);

        $subject = trim(filter_input(INPUT_POST, 'subject'));
        $this->context['subject'] = $subject;
        $ticket->setSubject($subject);

        $message = trim(filter_input(INPUT_POST, 'message'));
        $this->context['message'] = $message;
        $ticket->setMessage($message);

        if($ticketRepository->findOneBy(array('message' => $message)) != null) {
            $ticketValidator->fail(gettext('This message has already been sent.'));
        }

        try {
            $ticketValidator->validate($ticket);

            try {
                $flap = $this->flaps->getFlap('ticket');
                $flap->pushThrottlingStrategy(new \BehEh\Flaps\Throttling\LeakyBucketStrategy(2,
                    '60s'));
                $flap->pushThrottlingStrategy(new \BehEh\Flaps\Throttling\LeakyBucketStrategy(5,
                    '1h'));
                $flap->pushThrottlingStrategy(new \BehEh\Flaps\Throttling\LeakyBucketStrategy(10,
                    '24h'));
                $flap->limit($_SERVER['REMOTE_ADDR']);

                $this->manager->persist($ticket);
                $this->manager->flush();
                $this->success('contact', str_replace('%number%', $ticket->getId(), gettext('Thank you for your message.')));
                $this->context['hide_form'] = true;
            }
            catch(TooManyRequestsException $ex) {
                $this->error('contact', gettext('You already sent a message a short while ago.'));
            }
        } catch (ValidationException $ex) {
            $this->error('contact', implode('<br>', $ex->getFails()));
        }

        $this->get();
    }
}
