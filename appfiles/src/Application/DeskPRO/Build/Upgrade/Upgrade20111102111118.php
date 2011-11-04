<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111102111118 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE stat (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, parent_stat_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, starred TINYINT(1) NOT NULL, disabled TINYINT(1) NOT NULL, run_frequency VARCHAR(10) NOT NULL, last_run DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, UNIQUE INDEX UNIQ_20B8FF21F675F31B (author_id), UNIQUE INDEX UNIQ_20B8FF217B185B4D (parent_stat_id), PRIMARY KEY(id)) ENGINE = InnoDB;");
			App::getDb()->exec("ALTER TABLE stat ADD FOREIGN KEY (author_id) REFERENCES people(id);");
			App::getDb()->exec("ALTER TABLE stat ADD FOREIGN KEY (parent_stat_id) REFERENCES stat(id);");
			App::getDb()->exec("CREATE TABLE report_dashboard (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;");
			App::getDb()->exec("CREATE TABLE report_dashboard_stat (id INT AUTO_INCREMENT NOT NULL, report_dashboard_id INT DEFAULT NULL, stat_id INT DEFAULT NULL, chart_type VARCHAR(20) NOT NULL, grid_slots SMALLINT NOT NULL, slot_number SMALLINT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_67443C5EB7BADDB2 (report_dashboard_id), INDEX IDX_67443C5E9502F0B (stat_id), PRIMARY KEY(id)) ENGINE = InnoDB;");
			App::getDb()->exec("ALTER TABLE report_dashboard_stat ADD FOREIGN KEY (report_dashboard_id) REFERENCES report_dashboard(id);");			
			App::getDb()->exec("ALTER TABLE report_dashboard_stat ADD FOREIGN KEY (stat_id) REFERENCES stat(id);");

		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
