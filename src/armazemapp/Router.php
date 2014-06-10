<?php

namespace armazemapp;

class Router {

	const FB_APP_ROOT = "https://apps.facebook.com/armazemdorosario/";
	
	protected $referer = null;
	
	public function __construct() {
		$this->referer = $_SERVER['HTTP_REFERER'];
	}
	
	private function isUnderFacebookAppCanvas() {
		return strpos($this->referer, Router::FB_APP_ROOT) !== false;
	}
	
	private function generateURL($url) {
		if($this->isUnderFacebookAppCanvas()) {
			return Router::FB_APP_ROOT . $url;
		}
		else {
			return $url;
		}
	}
	
	/**
	 * Gera um atributo href e target de acordo com o ambiente no qual o aplicativo está
	 * 
	 * @example $router->generateLink('lista.php');
	 * 
	 * @param string $url URL
	 * @param boolean $echo Se verdadeiro, envia o código gerado para a saída. Se falso, apenas retorna. 
	 * @return string O código gerado. Só é retornado se $echo for true.
	 */
	public function generateLink($url, $echo = true) {
		$output .= ($this->isUnderFacebookAppCanvas()) ? 'target="_parent"' : 'target="_self"';
		$output .= ' href="' . $this->generateURL($url) . '"';
		
		if($echo) {
			echo $output;
		}
		else {
			return $output;
		}
	}
	
}