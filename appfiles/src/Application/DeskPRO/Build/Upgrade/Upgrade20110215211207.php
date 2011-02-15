<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110215211207 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Alter ticket_queues');

		try {
			App::getDb()->exec("ALTER TABLE  `ticket_queues` ADD  `sys_name` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `is_global` , ADD UNIQUE (`sys_name`)");
			App::getDb()->exec("ALTER TABLE  `ticket_queues` CHANGE  `person_id`  `person_id` INT( 11 ) NULL DEFAULT NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Insert system queues');

		try {
			$ticket_queues = array(
				array('order_by'=>'ticket.urgency:desc','person_id'=>null,'title'=>'Your Tickets','is_enabled'=>1,'is_global'=>1,'sys_name'=>'agent','terms'=>'a:2:{i:0;a:3:{s:9:\"rule_type\";s:5:\"agent\";s:2:\"op\";s:2:\"is\";s:5:\"agent\";s:2:\"-1\";}i:1;a:3:{s:9:\"rule_type\";s:6:\"status\";s:2:\"op\";s:2:\"is\";s:6:\"status\";s:4:\"open\";}}','group_by'=>''),
				array('order_by'=>'ticket.urgency:desc','person_id'=>null,'title'=>'Your Teams Tickets','is_enabled'=>1,'is_global'=>1,'sys_name'=>'agent_team','terms'=>'a:2:{i:0;a:3:{s:9:\"rule_type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:10:\"agent_team\";s:2:\"-1\";}i:1;a:3:{s:9:\"rule_type\";s:6:\"status\";s:2:\"op\";s:2:\"is\";s:6:\"status\";s:4:\"open\";}}','group_by'=>''),
				array('order_by'=>'ticket.urgency:desc','person_id'=>null,'title'=>'You\'re a Participant','is_enabled'=>1,'is_global'=>1,'sys_name'=>'participant','terms'=>'a:2:{i:0;a:3:{s:9:\"rule_type\";s:11:\"participant\";s:2:\"op\";s:2:\"is\";s:5:\"agent\";s:2:\"-1\";}i:1;a:3:{s:9:\"rule_type\";s:6:\"status\";s:2:\"op\";s:2:\"is\";s:6:\"status\";s:4:\"open\";}}','group_by'=>''),
				array('order_by'=>'ticket.urgency:desc','person_id'=>null,'title'=>'Unassigned','is_enabled'=>1,'is_global'=>1,'sys_name'=>'unassigned','terms'=>'a:2:{i:0;a:3:{s:9:\"rule_type\";s:5:\"agent\";s:2:\"op\";s:2:\"is\";s:5:\"agent\";s:1:\"0\";}i:1;a:3:{s:9:\"rule_type\";s:6:\"status\";s:2:\"op\";s:2:\"is\";s:6:\"status\";s:4:\"open\";}}','group_by'=>''),
				array('order_by'=>'ticket.urgency:desc','person_id'=>null,'title'=>'All','is_enabled'=>1,'is_global'=>1,'sys_name'=>'all','terms'=>'a:1:{i:0;a:3:{s:9:\"rule_type\";s:6:\"status\";s:2:\"op\";s:2:\"is\";s:6:\"status\";s:4:\"open\";}}','group_by'=>'')
			);

			App::getDb()->beginTransaction();
			foreach ($ticket_queues as $q) {
				App::getDb()->insert('ticket_queues', $q);
			}
			App::getDb()->commit();
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
