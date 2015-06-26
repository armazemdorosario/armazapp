<?php

namespace Eventzapp;

class Gender {

	public static function g($gender, $generalReturn, $returnForFemales, $returnForMales, $onlyReturn = false) {

		$returnValue = $generalReturn;

		switch($gender) {
			case 'female':
				$returnValue = $returnForFemales;
			break;

			case 'male':
				$returnValue = $returnForMales;
			break;

			default:
				$returnValue = $generalReturn;
			break;

		} // end switch

		if(!$onlyReturn) {
			echo $returnValue;
		} // end if not $onlyReturn

		return $returnValue;

	} // end public static function g

} // end class Eventazapp\Gender
