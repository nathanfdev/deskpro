<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110330024841 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create HardDeleteTickets worker job');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `worker_jobs`");
			App::getDb()->exec("
				CREATE TABLE `worker_jobs` (
				  `id` varchar(50) NOT NULL,
				  `worker_group` varchar(50) DEFAULT NULL,
				  `title` varchar(100) NOT NULL,
				  `description` varchar(100) NOT NULL,
				  `job_class` varchar(100) NOT NULL,
				  `data` longtext,
				  `run_interval` int(11) NOT NULL,
				  `last_run_date` datetime DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB
			");

			\Application\DeskPRO\App::getOrm()->beginTransaction();
			$j = new \Application\DeskPRO\Entity\WorkerJob();
			$j['id'] = 'hard_delete_tickets';
			$j['worker_group'] = 'hard_delete_tickets';
			$j['title'] = 'Hard Delete Tickets';
			$j['description'] = 'Processes tickets that were soft-deleted long ago and permanantly deletes them';
			$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\HardDeleteTickets';
			$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\HardDeleteTickets::DEFAULT_INTERVAL;
			\Application\DeskPRO\App::getOrm()->persist($j);
			\Application\DeskPRO\App::getOrm()->flush();
			\Application\DeskPRO\App::getOrm()->commit();
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
