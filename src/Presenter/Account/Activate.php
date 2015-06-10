<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\BadRequestException;

class Activate extends Presenter
{

    public function get($username)
    {
        $user = $this->manager->getRepository('Lorry\Model\User')->findOneBy(array('username' => $username));
        if (!$user) {
            throw new FileNotFoundException('user '.$username);
        }

        if(!$this->session->authenticated()) {
            $this->redirect('/login?returnto='.urlencode('/users/'.$username.'/activate?'.implode('&', array_map(function($a, $b) {
                return $a.'='.$b;
            }, array_keys($_GET), $_GET))));
            return;
        }

        $this->security->requireLogin();

        if($this->session->getUser() != $user) {
            throw new ForbiddenException('attempting to activate wrong user');
        }

        if(!$user->isActivated()) {
            $expires = filter_input(INPUT_GET, 'expires');
            $address = filter_input(INPUT_GET, 'address');

            $hash = filter_input(INPUT_GET, 'hash');
            if (empty($hash)) {
                throw new BadRequestException();
            }

            try {
                $expected = $this->security->signActivation($user, $expires,
                    $address);
            } catch (\InvalidArgumentException $ex) {
                throw new BadRequestException();
            }

            if (hash_equals($expected, $hash) !== true) {
                throw new ForbiddenException('hash does not match expected value');
            }

            if ($expires < time()) {
                throw new ForbiddenException('token expired');
            }

            if ($address != $user->getEmail()) {
                throw new ForbiddenException('token is for another email address');
            }

            $user->activate();
            $this->manager->flush();
        }

        $this->redirect('/settings?activated#contact');
    }
}
