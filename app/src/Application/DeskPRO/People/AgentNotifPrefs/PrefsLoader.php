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
 * @category Entities
 */

namespace Application\DeskPRO\People\AgentNotifPrefs;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;

class PrefsLoader
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	private $person;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $db;

	public function __construct(Person $person, EntityManager $em)
	{
		$this->person = $person;
		$this->em     = $em;
		$this->db     = $em->getConnection();
	}

	/**
	 * @return Prefs
	 */
	public function getPrefs()
	{
		$prefs = new Prefs();

		#------------------------------
		# Load filters
		#------------------------------

		$filters = $this->em->getRepository('DeskPRO:TicketFilter')->getFiltersForPerson($this->person);
		$filters = Arrays::keyFromData($filters, 'id');

		$sys_filters = array();
		$custom_filters = array();
		foreach ($filters as $f) {
			if ($f->sys_name) {
				if (strpos($f->sys_name, '_w_hold') !== false || strpos($f->sys_name, 'archive_') === 0) continue;
				$sys_filters[] = $f;
			} else {
				$custom_filters[] = $f;
			}
		}

		#------------------------------
		# Load filter subscriptions
		#------------------------------

		$filter_subs = $this->db->fetchAll("
			SELECT *
			FROM ticket_filter_subscriptions
			WHERE person_id = ?
		", array($this->person->id));

		foreach ($filter_subs as $info) {
			if (!isset($filters[$info['filter_id']])) continue;

			$filter = $filters[$info['filter_id']];
			$subs = array('email' => array(), 'alert' => array());

			foreach ($info as $k => $v) {
				if ($v && preg_match('#(email|alert)_(.*?)$#', $k, $m)) {
					$subs[$m[1]][$m[2]] = true;
				}
			}

			if ($subs['email']) {
				$prefs->setFilterSubs('email', $filter, $subs['email']);
			}
			if ($subs['alert']) {
				$prefs->setFilterSubs('alert', $filter, $subs['alert']);
			}
		}

		#------------------------------
		# Load filter sub override options
		#------------------------------

		$user_prefs = $this->db->fetchAllKeyValue("
			SELECT name, value_str
			FROM people_prefs
			WHERE person_id = ? AND value_str IS NOT NULL AND value_str != ''
		", array($this->person->id));

		$user_prefs = new OptionsArray($user_prefs);

		$prefs->setFilterNotifyPrefs('email', array(
			'override_all'     => (bool)$user_prefs->get('agent_notify_override.all.email'),
			'override_forward' => (bool)$user_prefs->get('agent_notify_override.forward.email'),
		));
		$prefs->setFilterNotifyPrefs('alert', array(
			'override_all'     => (bool)$user_prefs->get('agent_notify_override.all.alert'),
			'override_forward' => (bool)$user_prefs->get('agent_notify_override.forward.alert'),
		));

		#------------------------------
		# Load mention mode
		#------------------------------

		if ($user_prefs->get('agent_notif.ticket_mention') == 'smart_send') {
			$prefs->setEmailMentionMode(Prefs::SMART_SEND);
		} else {
			$prefs->setEmailMentionMode(Prefs::ALWAYS_SEND);
		}

		#------------------------------
		# Load apps
		#------------------------------

		// Apps are stored as preferences named:
		//     agent_notif.<app>_<pref>.<email|alert>
		// Eg:
		//     - agent_notif.tweet_assign_self.email
		//     - agent_notif.new_user.email
		//     - agent_notif.new_user.alert
		//     - agent_notif.login_attempt.email
		// The prefs object needs to set them under app/type then name, for example:
		//     $prefs->setAppSubs('email', 'crm', array('new_user' => true));
		//     $prefs->setAppSubs('alert', 'crm', array('new_user' => true));
		// Which is why we need this loopy loop to convert the structures a bit

		foreach (array(
			 'chat',
			 'task',
			 'twitter',
			 'feedback',
			 'publish',
			 'crm',
			 'account'
		) as $app_name) {
			foreach (array('email', 'alert') as $type) {
				$subs = $prefs->getAppSubs($type, $app_name);
				foreach (array_keys($subs) as $name) {
					$pref_name = "agent_notif.{$name}.$type";
					if ($user_prefs->get($pref_name)) {
						$subs[$name] = true;
					} else {
						$subs[$name] = false;
					}
				}
				$prefs->setAppSubs($type, $app_name, $subs);
			}
		}

		return $prefs;
	}


	/**
	 * @param array $filter_subs
	 * @param array $other_subs
	 * @return Prefs
	 */
	public function getPrefsFromArray(array $filter_subs = array(), array $other_subs = array())
	{
		$prefs = new Prefs();

		#------------------------------
		# Load filters
		#------------------------------

		if ($filter_subs) {
			$filters = $this->em->getRepository('DeskPRO:TicketFilter')->getFiltersForPerson($this->person);
			$filters = Arrays::keyFromData($filters, 'id');

			foreach ($filter_subs as $info) {
				$filter_id   = !empty($info['filter_id']) ? $info['filter_id'] : null;
				$email_types = !empty($info['email']) ? $info['email'] : array();
				$alert_types = !empty($info['alert']) ? $info['alert'] : array();

				if ($filter_id && !isset($filters[$filter_id])) {
					continue;
				}

				if ($email_types) {
					$email_types = array_combine($email_types, $email_types);
					$prefs->setFilterSubs('email', $filters[$filter_id], $email_types);
				}
				if ($alert_types) {
					$alert_types = array_combine($alert_types, $alert_types);
					$prefs->setFilterSubs('alert', $filters[$filter_id], $alert_types);
				}
			}
		}

		#------------------------------
		# Load others
		#------------------------------

		if ($other_subs) {
			$valid_apps = array(
				'chat' => true,
				'task' => true,
				'twitter' => true,
				'feedback' => true,
				'publish' => true,
				'crm' => true,
				'account' => true,
			);

			foreach ($other_subs as $info) {
				$app_name    = !empty($info['type']) ? $info['type'] : null;
				$email_types = !empty($info['email']) ? $info['email'] : array();
				$alert_types = !empty($info['alert']) ? $info['alert'] : array();

				if (!$app_name || !isset($valid_apps[$app_name])) {
					continue;
				}

				if ($email_types) {
					$email_types = array_combine($email_types, $email_types);
					$prefs->setAppSubs('email', $app_name, $email_types);
				}
				if ($alert_types) {
					$alert_types = array_combine($alert_types, $alert_types);
					$prefs->setAppSubs('alert', $app_name, $alert_types);
				}
			}
		}

		return $prefs;
	}
}