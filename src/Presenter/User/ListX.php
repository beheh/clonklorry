<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\Exception\FileNotFoundException;

class ListX extends Presenter
{

    public function get()
    {
        $this->security->requireLogin();

        $userRepository = $this->manager->getRepository('Lorry\Model\User');

        $filter = filter_input(INPUT_GET, 'filter');

        $page = isset($_GET['page']) ? filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) : 1;
        $width = 10;
        $this->context['page'] = $page;
        $first = ($page - 1)*$width;

        switch ($filter) {
            case 'administrators':
                $this->context['filter'] = gettext('Administrators');
                $users = $userRepository->getAllAdministrators($width, $first);
                break;
            case 'moderators':
                $this->context['filter'] = gettext('Moderators');
                $users = $userRepository->getAllModerators($width, $first);
                break;
            case '':
            case 'users':
                $users = $userRepository->getAll($width, $first);
                break;
            default:
                throw new FileNotFoundException();
                break;
        }

        $this->context['lastPage'] = 501;
        /*$this->context['lastPage'] = floor(count($users) / $width);
        echo $this->context['lastPage'];*/

        $this->context['users'] = $users;


        $this->display('user/list.twig');
    }
}
