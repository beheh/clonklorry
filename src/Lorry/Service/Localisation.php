<?php

namespace Lorry\Service;

use Lorry\Service;

class Localisation extends Service {

	public function localize() {
		//$language = http_negotiate_language(array('en-US'));
		$requested = 'de-DE';
		header('Content-Language: ' . $requested);

		$language = str_replace('-', '_', $requested);
		putenv('LC_ALL=' . $language);
		setlocale(LC_ALL, $language);

		$textdomain = 'lorryWeb-' . $language;
		bindtextdomain($textdomain, $this->lorry->getRootDir() . '../app/locale');
		bind_textdomain_codeset($textdomain, 'UTF-8');
		textdomain($textdomain);
	}

	public function namedMonth($month) {
		switch($month) {
			case 1:
				return gettext('January');
			case 2:
				return gettext('February');
			case 3:
				return gettext('March');
			case 4:
				return gettext('April');
			case 5:
				return gettext('May');
			case 6:
				return gettext('June');
			case 7:
				return gettext('July');
			case 8:
				return gettext('August');
			case 9:
				return gettext('September');
			case 10:
				return gettext('October');
			case 11:
				return gettext('November');
			case 12:
				return gettext('December');
		}
	}

	public function countedNumber($number) {
		switch($number) {
			case 1:
				return gettext('1st');
			case 2:
				return gettext('2nd');
			case 3:
				return gettext('3rd');
			default:
				return strtr(gettext('%n%th'), array('%n%' => $number));
		}
	}

}