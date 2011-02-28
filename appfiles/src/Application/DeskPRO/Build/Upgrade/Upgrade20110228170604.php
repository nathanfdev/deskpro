<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110228170604 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Changes to language table');

		try {
			App::getDb()->exec("ALTER TABLE `languages` DROP `locale`");
			App::getDb()->exec("ALTER TABLE `languages` DROP INDEX  `languages_parent_id_uniq`, ADD INDEX  `languages_parent_id_uniq` (  `parent_id` )");
			App::getDb()->exec("ALTER TABLE tickets DROP FOREIGN KEY tickets_ibfk_1");
			App::getDb()->exec("ALTER TABLE people DROP FOREIGN KEY people_ibfk_1");
			App::getDb()->exec("ALTER TABLE `tickets` DROP `language_id`");
			App::getDb()->exec("ALTER TABLE `people` DROP `language_id`");
		} catch (\Exception $e) { /* accept failue, depending on the dev install it might not have the index at all */ }

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Add locale table');

		try {
			App::getDb()->exec("CREATE TABLE locales (id INT AUTO_INCREMENT NOT NULL, language_id INT DEFAULT NULL, locale VARCHAR(20) NOT NULL, title VARCHAR(255) NOT NULL, INDEX locales_language_id_idx (language_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step3()
	{
		$this->output->writeln('Add locale_id to people table');

		try {
			App::getDb()->exec("ALTER TABLE  `people` ADD  `locale_id` INT NULL AFTER  `id` , ADD INDEX (  `locale_id` )");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step4()
	{
		$this->output->writeln('Insert default records');

		try {
			$lang1 = App::getDb()->fetchAllCol('SELECT id FROM languages WHERE id = 1');
			if ($lang1) {
				App::getDb()->exec("UPDATE `languages` SET  `title` =  'Default English' WHERE `id` = 1");
			} else {
				App::getDb()->exec("INSERT INTO `languages` (`id`, `parent_id`, `title`) VALUES (1, NULL, 'Default English')");
			}

			App::getDb()->exec("INSERT INTO `locales` (`id`, `language_id`, `locale`, `title`) VALUES (NULL, '1', 'en_US', 'English (US)')");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
