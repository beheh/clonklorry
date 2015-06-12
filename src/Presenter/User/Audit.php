<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Exception\FileNotFoundException;

class Audit extends AbstractPresenter
{

    /**
     *
     * @param string $username
     * @throws FileNotFoundException
     */
    public function get($username)
    {
        $this->security->requireAdministrator();

        /* @var $user \Lorry\Model\User */
        $user = $this->manager->getRepository('Lorry\Model\User')->findOneBy(array('username' => $username));

        if (!$user) {
            throw new FileNotFoundException('unknown user "'.$username.'"');
        }

        if ($user->getUsername() !== $username) {
            $this->redirect($this->config->get('base').'/users/'.$user->getUsername().'/audit', true);
            return;
        }

        $this->context['username'] = $user->getUsername();
        $this->context['self'] = $this->session->authenticated() && $user->getId() == $this->session->getUser()->getId();

        $this->context['administrator'] = $user->isAdministrator();
        $this->context['moderator'] = $user->isModerator();

        $this->context['moderations'] = $user->getExecutedModerations();

        $this->display('user/audit.twig');
    }
}
