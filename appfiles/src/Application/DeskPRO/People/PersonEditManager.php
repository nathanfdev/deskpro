<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Mail
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;

use Doctrine\ORM\EntityManager;

use Orb\Util\Strings;
use Orb\Util\Util;

class PersonEditManager
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
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
	}

	public function deleteUser(Person $person)
	{
		$this->em->beginTransaction();

		try {
			$this->em->remove($person);
			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}
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
			'new_idea.email', 'new_idea.alert',
			'new_idea_validate.email', 'new_idea_validate.alert',
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
}
