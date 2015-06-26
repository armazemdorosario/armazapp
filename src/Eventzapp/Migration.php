<?php

namespace Eventazapp;

class Migration {

	private static $changes = array(
		'v1.0.0 v2.0.0' => array(
			'ALTER TABLE `users` CHANGE `timestamp` `date_created` DATETIME NOT NULL;',
			'ALTER TABLE `benefits` CHANGE `timestamp` `b_date_created` DATETIME NOT NULL;',
			'ALTER TABLE `users_benefits` CHANGE `timestamp` `ub_date_created` DATETIME NOT NULL;',
			'ALTER TABLE `users` ADD `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;',
			'ALTER TABLE `users` CHANGE `identidade` `id_card` VARCHAR( 28 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;',
			'ALTER TABLE `users` ADD `ir_number` BIGINT( 11 ) UNSIGNED NULL, ADD UNIQUE (`ir_number`);',
		),
	);

	private static $views = array(
		'vw_users_benefits' => 'select `gagito_armazemapp`.`users`.`fbid` AS `fbid`,`gagito_armazemapp`.`users`.`name` AS `name`,`gagito_armazemapp`.`users`.`id_card` AS `id_card`,`gagito_armazemapp`.`users`.`fbname` AS `fbname`,`gagito_armazemapp`.`users`.`fbgender` AS `fbgender`,`gagito_armazemapp`.`users`.`access_level` AS `access_level`,`gagito_armazemapp`.`users`.`trust_level` AS `trust_level`,`gagito_armazemapp`.`users_benefits`.`userfbid` AS `userfbid`,`gagito_armazemapp`.`users_benefits`.`ub_date_created` AS `ub_date_created`,`gagito_armazemapp`.`users_benefits`.`eventfbid` AS `eventfbid`,`gagito_armazemapp`.`users_benefits`.`private` AS `private`,`gagito_armazemapp`.`users_benefits`.`benefit` AS `benefit`,`gagito_armazemapp`.`users_benefits`.`chosen` AS `chosen`,`gagito_armazemapp`.`users_benefits`.`actually_attended` AS `actually_attended`,`gagito_armazemapp`.`benefits`.`status` AS `status`,`gagito_armazemapp`.`users_benefits`.`chosen_by_fbid` AS `chosen_by_fbid` from ((`gagito_armazemapp`.`users` join `gagito_armazemapp`.`users_benefits` on((`gagito_armazemapp`.`users`.`fbid` = `gagito_armazemapp`.`users_benefits`.`userfbid`))) join `gagito_armazemapp`.`benefits` on(((`gagito_armazemapp`.`benefits`.`eventfbid` = `gagito_armazemapp`.`users_benefits`.`eventfbid`) and (`gagito_armazemapp`.`benefits`.`benefit_type` = `gagito_armazemapp`.`users_benefits`.`benefit`))))';
		'vw_users_stats' => 'select `vw_users_benefits`.`fbid` AS `fbid`,`vw_users_benefits`.`name` AS `name`,`vw_users_benefits`.`benefit` AS `benefit`,`vw_users_benefits`.`chosen` AS `chosen`,`vw_users_benefits`.`actually_attended` AS `actually_attended`,count(0) AS `count` from `gagito_armazemapp`.`vw_users_benefits` where (`vw_users_benefits`.`ub_date_created` >= (curdate() - interval 15 day)) group by `vw_users_benefits`.`fbid`,`vw_users_benefits`.`benefit`,`vw_users_benefits`.`chosen`,`vw_users_benefits`.`actually_attended` order by `vw_users_benefits`.`fbid`';
	);
	
}
