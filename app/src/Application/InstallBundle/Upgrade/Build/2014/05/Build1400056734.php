<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\TicketEscalation;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\InstallBundle\Upgrade\Build\Helper201405\TriggerActionConverter;
use Orb\Util\Dates;

class Build1400056734 extends AbstractBuild
{
	/**
	 * @var TriggerActionConverter
	 */
	private $action_converter;

	public function run()
	{
		require_once DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/2014/05/Helper/TriggerActionConverter.php';

		$this->out("Upgrading escalations");

		$db = $this->container->getDb();
		$db->executeUpdate('DELETE FROM ticket_escalations');

		#------------------------------
		# Init helpers
		#------------------------------

		$gateway_addr_map = $this->getUpgradeData('201404', 'gateway_address_map') ?: array();

		$mappings = array(
			'gateway_address_to_email_account' => $gateway_addr_map
		);

		$this->action_converter = new TriggerActionConverter($mappings);

		#------------------------------
		# Process old escalations
		#------------------------------

		$old_escalations = $this->getUpgradeData('201404', 'ticket_triggers') ?: array();

		$id_map = array();

		foreach ($old_escalations as $esc) {
			// We only care about escalations
			if (strpos($esc['event_trigger'], 'time') === false) {
				continue;
			}

			$this->out("Processing #{$esc['id']} ...");
			$new_esc = $this->processTrigger($esc);
			if ($new_esc) {
				$this->container->getEm()->persist($new_esc);
				$this->container->getEm()->flush();
				$this->out("-- Saved");

				$id_map[$esc['id']] = $new_esc->id;
			} else {
				$this->out("-- Skipped");
			}
		}

		if ($id_map) {
			$this->saveUpgradeData('201404', 'esc_id_map', $id_map);
		}
	}


	/**
	 * @param array $old_esc
	 * @return TicketTrigger|null
	 */
	private function processTrigger(array $old_esc)
	{
		// Default triggers just turn on depending on the status of the old default triggers
		if ($old_esc['sys_name']) {
			return null;
		}

		$old_esc['event_trigger_options'] = @unserialize($old_esc['event_trigger_options']) ?: array();
		$old_esc['terms']     = @unserialize($old_esc['terms']) ?: array();
		$old_esc['terms_any'] = @unserialize($old_esc['terms_any']) ?: array();
		$old_esc['actions']   = @unserialize($old_esc['actions']) ?: array();

		if (!$old_esc['event_trigger_options'] || empty($old_esc['event_trigger_options']['time'])) {
			$this->out("-- No or bad time option");
			return null;
		}

		#------------------------------
		# Update actions
		#------------------------------

		$is_incomplete = false;

		$actions_set = new TriggerActions();

		foreach ($old_esc['actions'] as $act) {
			$new_act = $this->action_converter->getTriggerAction($act);
			if ($new_act) {
				if (is_array($new_act)) {
					foreach ($new_act as $a) {
						$actions_set->addAction($a);
					}
				} else {
					$actions_set->addAction($new_act);
				}
			} else {
				$this->out("-- Skipped action {$act['type']}");
				$is_incomplete = true;
			}
		}

		if (!count($actions_set)) {
			$this->out("-- Skipping no action escalation");
			return null;
		}

		#------------------------------
		# Create trigger object
		#------------------------------

		$esc = new TicketEscalation();
		$esc->event_trigger = $old_esc['event_trigger'];
		$esc->event_trigger_time = $this->getTimeSeconds($old_esc['event_trigger_options']['time']);
		$esc->date_created  = new \DateTime();
		$esc->date_last_run = new \DateTime();

		if (!empty($old_esc['date_created'])) {
			try {
				$d = \DateTime::createFromFormat('Y-m-d H:i:s', $old_esc['date_created']);
				if ($d) {
					$esc->date_created = $d;
				}
			} catch (\Exception $e) {}
		}

		$esc->title      = $old_esc['title'] ?: 'Trigger ' . $old_esc['id'];
		if ($is_incomplete) {
			$esc->title .= ' (REQUIRES REVIEW)';
		}
		$esc->is_enabled = (bool)$old_esc['is_enabled'] && !$is_incomplete;
		$esc->terms      = $old_esc['terms'];
		$esc->terms_any  = $old_esc['terms_any'];
		$esc->actions    = $actions_set;

		return $esc;
	}


	/**
	 * @param $time_with_unit
	 * @return int
	 */
	private function getTimeSeconds($time_with_unit)
	{
		list ($time, $scale) = explode(' ', $time_with_unit);

		switch ($scale) {
			case 'minutes':
				$secs = $time * Dates::SECS_MIN;
				break;
			case 'hours':
				$secs = $time * Dates::SECS_HOUR;
				break;
			case 'days':
				$secs = $time * Dates::SECS_DAY;
				break;
			case 'weeks':
				$secs = $time * Dates::SECS_WEEK;
				break;
			case 'months':
				$secs = $time * Dates::SECS_MONTH;
				break;
			default:
				$secs = $time;
				break;
		}

		return $secs;
	}
}