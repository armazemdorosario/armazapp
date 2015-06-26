<?php

namespace Eventazapp {

	class Locale {
		
		private $directory;
		private $putenv;
		private $setlocale;
		private $bindtextdomain;
		private $textdomain;
		private $bind_textdomain_codeset;
		
		public function __construct($locale, $domain) {
			$this->directory = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'languages';
			$this->putenv = putenv("LANG=" . $locale);
			$this->setlocale = setlocale(LC_ALL, $locale);
			if (is_dir($this->directory) && function_exists(  'bindtextdomain' ) ) {
				$this->bindtextdomain = bindtextdomain($domain, $this->directory);
			}
			if( function_exists( 'textdomain' ) ) {
				$this->textdomain = textdomain($domain);
			}
			if( function_exists( 'bind_textdomain_codeset' ) ) {
			$this->bind_textdomain_codeset = bind_textdomain_codeset($domain, 'UTF-8');
			}
		}

		public function smarty_translate($params, $smarty) {
			$msgid = isset($params['msgid']) && !empty($params['msgid']) ? $params['msgid'] : '';
			return _($msgid);
		}

	}

}

if ( !function_exists( '_' ) ) {
	function _( $msgid ) {
		return $msgid;
	}
}