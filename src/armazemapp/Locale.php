<?php

namespace armazemapp;

/**
 *
 * @author Jimmy
 *        
 */
class Locale {
	
	private $directory;
	private $putenv;
	private $setlocale;
	private $bindtextdomain;
	private $textdomain;
	private $bind_textdomain_codeset;
	
	/**
	 */
	function __construct($locale, $domain) {
		$this->directory = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'locales';
		$this->putenv = putenv("LANG=" . $locale);
		$this->setlocale = setlocale(LC_ALL, $locale);
		if (is_dir($this->directory)) {
			$this->bindtextdomain = bindtextdomain($domain, $this->directory);
		}
		$this->textdomain = textdomain($domain);
		$this->bind_textdomain_codeset = bind_textdomain_codeset($domain, 'UTF-8');
	}
}

?>