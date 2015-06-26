<?php

namespace Eventzapp;

class Debugger {
	
	public static function log($message) {
		switch (getenv('ENV')) {
			case 'development':
				echo '<pre>'.$message.'</pre>';
				break;
			
			default:
				error_log($message);
				break;
		}
	}

}
