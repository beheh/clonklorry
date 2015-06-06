<?php

namespace Lorry\Presenter\Manage\Administrator;

use Lorry\Presenter;

class Portal extends Presenter
{

    public function get()
    {
        $this->security->requireAdministrator();
        $this->offerIdentification();
        $this->security->requireIdentification();
        
        $this->context['statistics'] = $this->config->get('admin/statistics');

        $this->display('manage/administrator/portal.twig');
    }
}
