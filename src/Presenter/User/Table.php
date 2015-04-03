<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\Model\User;

class Table extends Presenter
{

    public function get()
    {
        $this->security->requireLogin();

        $users = $this->persistence->build('User');

        $filter = filter_input(INPUT_GET, 'filter');
        switch ($filter) {
            case 'administrators':
                $this->context['filter'] = gettext('Administrators');
                $users = $users->all()->byPermission(User::PERMISSION_ADMINISTRATE);
                break;
            case 'moderators':
                $this->context['filter'] = gettext('Moderators');
                $users = $users->all()->byPermission(User::PERMISSION_MODERATE);
                break;
            case '':
            case 'users':
                $users = $users->all()->byAnything();
                break;
            default:
                throw new FileNotFoundException();
                break;
        }
        foreach ($users as $user) {
            $this->context['users'][] = array(
                'name' => $user->getUsername(),
                'administrator' => $user->isAdministrator(),
                'moderator' => $user->isModerator()
            );
        }


        $this->display('user/table.twig');
    }
}
