<?php

namespace Eventzapp;

class Debugger {

	public static function log($message) {

		$env = getenv('ENV');
		$env = empty($env) ? 'development' : $env;

		switch ($env) {
			case 'development':
				echo '<pre>'.$message.'</pre>' . "\n";
				break;

			default:
				error_log($message);
				break;
		}
	}

}
