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
 * @category Mail
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;

use Doctrine\ORM\EntityManager;

use Orb\Util\Strings;
use Orb\Util\Util;

class PersonEditManager implements PersonContextInterface
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * Who is performing these edits
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
	}

	public function deleteUser(Person $person)
	{
		$purger = new Purger($person, $this->em);
		if ($this->person_context) {
			$purger->setPersonContext($this->person_context);
		}
		$purger->purge();
	}

	public function mergeUsers(Person $person, Person $other_person)
	{

	}

	/**
	 * Save general notification preferences
	 *
	 * $prefs is an array(pref=>true, pref=>true)
	 *
	 * @throws \Exception
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param array $prefs
	 * @return void
	 */
	public function saveNotificationPreferences(Person $person, array $prefs)
	{
		$valid_names = array(
			'chat_message.email',
			'new_feedback.email', 'new_feedback.alert',
			'new_feedback_validate.email', 'new_feedback_validate.alert',
			'new_comment.email', 'new_comment.alert',
			'new_comment_validate.email', 'new_comment_validate.alert',
			'new_user.email', 'new_user.alert',
			'new_user_validate.email', 'new_user_validate.alert',
			'login_attempt.email', 'login_attempt_fail.email',
		);

		$this->em->beginTransaction();

		$new_prefs = array();

		try {

			// Clear out old preferences
			$this->db->executeQuery("
				DELETE FROM people_prefs
				WHERE person_id = ? AND `name` LIKE 'agent_notif.%'
			", array($person->id));

			// Rebuild new ones
			foreach ($prefs as $name => $checked) {
				if (!$checked || !in_array($name, $valid_names)) continue;

				$pref = new \Application\DeskPRO\Entity\PersonPref();
				$pref->person = $person;
				$pref->name = "agent_notif.{$name}";
				$pref->value_str = "1";

				$this->em->persist($pref);

				$new_prefs[] = $pref;
			}

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}
	}

	/**
	 * Save subscriptions on a filter. Only agents.
	 *
	 * $subs is array(filter_id => array(type=>true, type=>true, type=>true)
	 *
	 * @throws \Exception
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param array $subs
	 * @return array
	 */
	public function saveFilterSubscriptions(Person $person, array $subs)
	{
		$valid_names = array(
			'email_new', 'email_user_activity', 'email_agent_activity', 'email_property_change',
			'alert_new', 'alert_user_activity', 'alert_agent_activity', 'alert_property_change',
		);

		$filter_info = App::getApi('tickets.filters')->getGroupedFiltersForPerson($person);

		$this->em->beginTransaction();

		$new_subs = array();

		try {

			// First delete all the ones the user has now, we're just gonna rebuild
			$this->db->delete('ticket_filter_subscriptions', array('person_id' => $person->id));

			foreach ($filter_info['all_filters'] as $filter) {
				if (!isset($subs[$filter->id])) continue;

				$props = array();
				foreach ($valid_names as $k) {
					if (isset($subs[$filter->id][$k]) && $subs[$filter->id][$k]) {
						$props[$k] = true;
					}
				}

				if ($props) {
					$sub = new \Application\DeskPRO\Entity\TicketFilterSubscription();
					$sub->filter = $filter;
					$sub->person = $person;

					foreach ($props as $k => $v) {
						$sub->$k = $v;
					}

					$new_subs[] = $sub;

					$this->em->persist($sub);
				}
			}

			$this->em->flush();
			$this->em->commit();

		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $new_subs;
	}


	/**
	 * Set the context (who is making these edits)
	 *
	 * @param Person $person
	 */
	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function getPersonContext()
	{
		return $this->person_context;
	}
}
