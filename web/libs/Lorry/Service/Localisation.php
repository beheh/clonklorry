<?php

class Lorry_Service_Localisation extends Lorry_Service {

	public function localize() {
		$language = http_negotiate_language(array('en-US'));
		header('Content-Language: '.$language);

		$language_gettext = str_replace('-', '_', $language);
		putenv('LC_ALL='.$language_gettext);
		setlocale(LC_ALL, $language_gettext);

		$textdomain = 'lorryWeb-'.$language_gettext;
		bindtextdomain($textdomain, ROOT.'libs/Lorry/Locale');
		bind_textdomain_codeset($textdomain, 'UTF-8');
		textdomain($textdomain);
	}

}