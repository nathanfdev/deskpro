<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111111105208 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `StatValue`");
			App::getDb()->exec("DROP TABLE IF EXISTS `Stat`");

			App::getDb()->exec("CREATE TABLE stat (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, parent_stat_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, grouping_ref VARCHAR(255) NOT NULL, stat_concept_class VARCHAR(500) NOT NULL, starred TINYINT(1) NOT NULL, disabled TINYINT(1) NOT NULL, run_frequency VARCHAR(10) NOT NULL, last_run DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, INDEX IDX_20B8FF21F675F31B (author_id), INDEX IDX_20B8FF217B185B4D (parent_stat_id), PRIMARY KEY(id)) ENGINE = InnoDB;");
			App::getDb()->exec("ALTER TABLE stat ADD FOREIGN KEY (author_id) REFERENCES people(id);");
			App::getDb()->exec("ALTER TABLE stat ADD FOREIGN KEY (parent_stat_id) REFERENCES stat(id);");

			App::getDb()->exec("CREATE TABLE stat_value (id INT AUTO_INCREMENT NOT NULL, stat_id INT DEFAULT NULL, value NUMERIC(10, 0) NOT NULL, stat_unix INT NOT NULL, INDEX IDX_715085229502F0B (stat_id), PRIMARY KEY(id)) ENGINE = InnoDB;");
			App::getDb()->exec("ALTER TABLE stat_value ADD FOREIGN KEY (stat_id) REFERENCES stat(id);");

			App::getDb()->exec("CREATE TABLE stat_value_group (id INT AUTO_INCREMENT NOT NULL, stat_value_id INT DEFAULT NULL, value NUMERIC(10, 0) NOT NULL, stat_unix INT NOT NULL, INDEX IDX_4AA43E4370176A35 (stat_value_id), PRIMARY KEY(id)) ENGINE = InnoDB;");
			App::getDb()->exec("ALTER TABLE stat_value_group ADD FOREIGN KEY (stat_value_id) REFERENCES stat_value(id);");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
