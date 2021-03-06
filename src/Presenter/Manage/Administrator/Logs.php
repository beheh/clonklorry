<?php

namespace Lorry\Presenter\Manage\Administrator;

use Lorry\Environment;
use Lorry\Presenter\AbstractPresenter;

class Logs extends AbstractPresenter
{

    public function get()
    {
        $this->security->requireAdministrator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $application_log = Environment::PROJECT_ROOT.'/logs/lorry.log';

        $this->context['application_log'] = is_readable($application_log) ? file_get_contents($application_log)
                : 'Can\'t read file at "'.$application_log.'"';

        $this->display('manage/administrator/logs.twig');
    }
}
