<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111116151738 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 0");
			App::getDb()->exec("DELETE FROM languages");
			App::getDb()->exec("DROP TABLE languages");
			App::getDb()->exec("CREATE TABLE languages (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, language_package VARCHAR(255) NOT NULL, locale VARCHAR(8) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("INSERT INTO `languages` (`id`, `title`, `language_package`, `locale`) VALUES (NULL, 'English', 'DeskproLanguages\\DeskPRO\\LangPackage', 'en_US')");
			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 1");
		} catch (\Exception $e) {

		}

		return Upgrader::STEP_DONE;
	}
}
