<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Urgency;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TicketTrigger;

class UrgencyOptions
{
	public $base_urgency = 1;
	public $time_options = array();
	public $user_options = array();

	public static function newFromSystemTriggers()
	{
		$triggers = App::getEntityRepository('DeskPRO:TicketTrigger')->getSystemTriggers('urgency');
		return new self($triggers);
	}

	public function __construct($triggers = null)
	{
		$this->time_options = array(
			'user_waiting' => 0,
			'user_waiting_num' => 0,

			'user_reply' => 0,
			'user_reply_num' => 0,

			'open' => 0,
			'open_num' => 0,
		);

		foreach (range(1,5) as $imp) {
			$this->user_options["importance_num_$imp"] = 0;
		}

		if ($triggers) {
			foreach ($triggers as $trigger) {
				$this->initFromSystemTrigger($trigger);
			}
		}
	}

	public function initFromSystemTrigger(TicketTrigger $trigger)
	{
		#------------------------------
		# Base urgency
		#------------------------------

		if ($trigger['sys_name'] == 'urgency.base') {
			$action_info = $trigger->getActionInfoOfType('urgency_set');
			$this->base_urgency = $action_info['num'];

		#------------------------------
		# Time-based setters
		#------------------------------

		} elseif (strpos($trigger['sys_name'], 'urgency.time_') !== false) {
			$key = preg_replace('#^urgency\.time_#', '', $trigger['sys_name']);
			$this->time_options[$key] = $trigger['event_trigger_option'];

			$action_info = $trigger->getActionInfoOfType('urgency');

			$this->time_options["{$key}_num"] = $action_info['num'];

		#------------------------------
		# User importance setters
		#------------------------------

		} elseif (strpos($trigger['sys_name'], 'urgency.user_importance_') !== false) {
			$key = preg_replace('#^urgency\.user_importance_#', '', $trigger['sys_name']);
			$action_info = $trigger->getActionInfoOfType('urgency');
			$this->user_options["importance_num_{$key}"] = $action_info['num'];
		}
	}

	public function save()
	{
		$triggers = App::getEntityRepository('DeskPRO:TicketTrigger')->getSystemTriggers('urgency');

		#------------------------------
		# Base urgency
		#------------------------------

		if (isset($triggers['urgency.base'])) {
			$tr = $triggers['urgency.base'];
		} else {
			$tr = new TicketTrigger();
			$tr['sys_name'] = 'urgency.base';
			$tr['event_trigger'] = TicketTrigger::EVENT_NEW_TICKET;
			$triggers[] = $tr;
		}

		$tr['actions'] = array(
			array('type' => 'urgency_set', 'options' => array('num' => $this->base_urgency))
		);

		#------------------------------
		# Time-based setters
		#------------------------------

		foreach (array('user_waiting', 'user_reply', 'open') as $key) {
			$sys_name = 'urgency.time_' . $key;
			if (isset($triggers[$sys_name])) {
				$tr = $triggers[$sys_name];
			} else {
				$tr = new TicketTrigger();
				$tr['sys_name'] = $sys_name;
				$tr['event_trigger'] = 'time_' . $key;
				$triggers[] = $tr;
			}

			$tr['event_trigger_option'] = $this->time_options[$key];
			$tr['actions'] = array(
				array('type' => 'urgency', 'options' => array('num' => $this->time_options["{$key}_num"]))
			);
		}

		#------------------------------
		# User importance setters
		#------------------------------

		foreach (range(1,5) as $key) {
			$sys_name = 'urgency.user_importance_' . $key;
			if (isset($triggers[$sys_name])) {
				$tr = $triggers[$sys_name];
			} else {
				$tr = new TicketTrigger();
				$tr['sys_name'] = $sys_name;
				$tr['event_trigger'] = TicketTrigger::EVENT_NEW_TICKET;
				$triggers[] = $tr;
			}

			$tr['terms'] = array(
				array('type' => 'user_importance', 'num' => $key)
			);

			$tr['actions'] = array(
				array('type' => 'urgency', 'options' => array('num' => $this->user_options["importance_num_{$key}"]))
			);
		}


		#------------------------------
		# Save
		#------------------------------

		App::getOrm()->transactional(function() use ($triggers) {
			foreach ($triggers as $tr) {
				$tr['title'] = $tr['sys_name'];
				App::getOrm()->persist($tr);
			}
			App::getOrm()->flush();
		});

		return $triggers;
	}
}
