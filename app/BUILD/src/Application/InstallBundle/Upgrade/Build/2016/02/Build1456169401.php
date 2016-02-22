<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1456169401 extends AbstractBuild
{
    public function run()
    {
        $this->execMutateSql('
CREATE TABLE `api_key_limits` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`limit` INT(11) NOT NULL,
	`current` INT(11) NOT NULL,
	`start_time` DATETIME NULL DEFAULT NULL,
	`interval` INT(11) NOT NULL,
	`type` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
	`api_key_id` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `IDX_9A50A5F68BE312B3` (`api_key_id`),
	CONSTRAINT `FK_9A50A5F68BE312B3` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE CASCADE
)
COLLATE=\'utf8_unicode_ci\'
ENGINE=InnoDB;

INSERT INTO `api_key_limits` (`limit`, `current`, `interval`, `type`) VALUES (500, 500, NULL, 3600, \'global\');
INSERT INTO `api_key_limits` (`limit`, `current`, `interval`, `type`) VALUES (2500, 2500, NULL, 86400, \'global\');
');
    }
}
