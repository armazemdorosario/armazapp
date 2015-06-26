<?php

namespace Eventzapp;

class Exception extends \Exception {
	
	public function __construct($message, $code = 0, \Exception $previous = null) {
		Debugger::log('Exception code ' . $code . ' (' . $message . ')');
		parent::__construct($message, $code, $previous);
	}

}
