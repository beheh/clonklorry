<?php

namespace Lorry\Presenter\Manage\Administrator;

use Lorry\Environment;
use Lorry\Presenter;

class Logs extends Presenter {

	public function get() {
		$this->security->requireAdministrator();

		$emergency_log = Environment::PROJECT_ROOT.'/logs/emergency.log';
		$application_log = Environment::PROJECT_ROOT.'/logs/lorry.log';
		
		$this->context['emergency_log'] = is_readable($emergency_log) ? file_get_contents($emergency_log) : 'Can\'t read file at "'.$emergency_log.'"';
		$this->context['application_log'] = is_readable($application_log) ? file_get_contents($application_log) : 'Can\'t read file at "'.$application_log.'"';

		$this->display('manage/administrator/logs.twig');
	}

}
