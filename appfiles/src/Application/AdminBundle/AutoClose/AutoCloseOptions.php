<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\AutoClose;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TicketTrigger;

class AutoCloseOptions
{
	public $resolve_agent_reply  = 432000; // 5 days
	public $close_agent_reply    = 1296000; // 15 days
	public $resolve_user_reply   = 5259487; // 2 months
	public $close_user_reply     = 7889231; // 3 months

	public static function newFromSystemTriggers()
	{
		$triggers = App::getEntityRepository('DeskPRO:TicketTrigger')->getSystemTriggers('auto_close');
		return new self($triggers);
	}

	public function __construct($triggers = null)
	{
		if ($triggers) {
			foreach ($triggers as $trigger) {
				$this->initFromSystemTrigger($trigger);
			}
		}
	}

	public function initFromSystemTrigger(TicketTrigger $trigger)
	{
		$type = str_replace('auto_close.', '', $trigger['sys_name']);
		$this->$type = $trigger['event_trigger_option'];
	}

	public function save()
	{
		$triggers = App::getEntityRepository('DeskPRO:TicketTrigger')->getSystemTriggers('auto_close');

		#------------------------------
		# resolve_agent_reply
		#------------------------------

		if (isset($triggers['auto_close.resolve_agent_reply'])) {
			$tr = $triggers['auto_close.resolve_agent_reply'];
		} else {
			$tr = new TicketTrigger();
			$tr['sys_name'] = 'auto_close.resolve_agent_reply';
			$tr['event_trigger'] = TicketTrigger::EVENT_TIME_AGENT_WAITING;
			$triggers[] = $tr;
		}

		$tr['terms'] = array('type' => 'status', 'options' => array('status' => 'awaiting_user'));
		$tr['event_trigger_option'] = $this->resolve_agent_reply;

		#------------------------------
		# close_agent_reply
		#------------------------------

		if (isset($triggers['auto_close.close_agent_reply'])) {
			$tr = $triggers['auto_close.close_agent_reply'];
		} else {
			$tr = new TicketTrigger();
			$tr['sys_name'] = 'auto_close.close_agent_reply';
			$tr['event_trigger'] = TicketTrigger::EVENT_TIME_AGENT_WAITING;
			$triggers[] = $tr;
		}

		$tr['terms'] = array('type' => 'status', 'options' => array('status' => 'awaiting_user'));
		$tr['event_trigger_option'] = $this->resolve_agent_reply;

		#------------------------------
		# resolve_user_reply
		#------------------------------

		if (isset($triggers['auto_close.resolve_user_reply'])) {
			$tr = $triggers['auto_close.resolve_user_reply'];
		} else {
			$tr = new TicketTrigger();
			$tr['sys_name'] = 'auto_close.resolve_user_reply';
			$tr['event_trigger'] = TicketTrigger::EVENT_TIME_USER_WAITING;
			$triggers[] = $tr;
		}

		$tr['terms'] = array('type' => 'status', 'options' => array('status' => 'awaiting_agent'));
		$tr['event_trigger_option'] = $this->resolve_agent_reply;

		#------------------------------
		# close_user_reply
		#------------------------------

		if (isset($triggers['auto_close.close_user_reply'])) {
			$tr = $triggers['auto_close.close_user_reply'];
		} else {
			$tr = new TicketTrigger();
			$tr['sys_name'] = 'auto_close.resolve_user_reply';
			$tr['event_trigger'] = TicketTrigger::EVENT_TIME_USER_WAITING;
			$triggers[] = $tr;
		}

		$tr['terms'] = array('type' => 'status', 'options' => array('status' => 'awaiting_agent'));
		$tr['event_trigger_option'] = $this->close_user_reply;


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