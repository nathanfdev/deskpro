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
 * @category Entities
 */

namespace Application\DeskPRO\People\Agents;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\ORM\CollectionHelper;
use Application\DeskPRO\Validator\Constraints as DeskproConstraints;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;
use Orb\Util\PhoneNumbers;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class EditAgent
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	private $agent;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $override_name;

	/**
	 * @var string[]
	 */
	public $zones;

	/**
	 * @var string[]
	 */
	public $emails;

	/**
	 * @var string
	 */
	public $primary_phone_number_text;

	/**
	 * @var \Application\DeskPRO\Entity\AgentTeam[]
	 */
	public $teams;

	/**
	 * @var \Application\DeskPRO\Entity\Usergroup[]
	 */
	public $agent_groups;

	/**
	 * @var \Application\DeskPRO\Entity\AgentTeam
	 */
	public $primary_team;

	/**
	 * @var array
	 */
	public $notification_settings;


	/**
	 * @param Person $person
	 */
	public function __construct(Person $person)
	{
		$this->agent = $person;
		$this->name = $person->name;
		$this->override_name = $person->override_display_name;
		$this->primary_phone_number = $person->getPrimaryPhoneNumber() ?: new PhoneNumber();
		$this->primary_phone_number_text = $person->getPrimaryPhoneNumberText();

		$this->zones = array();
		if ($person->can_admin) {
			$this->zones[] = 'admin';
		}
		if ($person->can_reports) {
			$this->zones[] = 'reports';
		}

		$this->emails = array();
		if ($person->primary_email) {
			$this->emails[] = $person->primary_email->email;
		}
		foreach ($person->emails as $email) {
			$this->emails[] = $email;
		}
		$this->emails = array_unique($this->emails);

		$this->teams = array();

		if ($person->id) {
			$person->loadHelper('AgentTeam');
			$this->teams = $person->getHelper('AgentTeam')->getAgentTeams();
		}

		$this->agent_groups = $person->usergroups->toArray();

		$this->notification_settings = array(
			'no_allow_set_email' => (int) $person->getPref('agent_notif.no_allow_set_email'),
			'no_allow_set_browser' => (int) $person->getPref('agent_notif.no_allow_set_browser'),
		);

		$this->primary_team = $person->primary_team;
	}


	/**
	 * Saves the agent.
	 *
	 * @param EntityManager $em
	 * @return Person
	 */
	public function save(EntityManager $em)
	{
		$agent = $this->agent;

		$em->persist($agent);

		#------------------------------
		# General props
		#------------------------------

		$agent->is_user               = true;
		$agent->is_confirmed          = true;
		$agent->is_agent              = true;
		$agent->can_agent             = true;

		$agent->name                  = $this->name;
		$agent->override_display_name = $this->override_name ?: '';

		if (!PhoneNumbers::looksEmpty($this->primary_phone_number_text)) {
			$this->primary_phone_number->number = $this->primary_phone_number_text;
			$agent->setPrimaryPhoneNumber($this->primary_phone_number);
		} else {
			$agent->setPrimaryPhoneNumber(null);
		}

		$agent->can_admin             = in_array('admin', $this->zones);
		$agent->can_reports           = in_array('reports', $this->zones);

		#------------------------------
		# Teams
		#------------------------------

		foreach ($agent->teams as $team) {
			/** @var $team AgentTeam */
			$team->removePerson($agent); // unidirectional
		}

		$found_primary = false;

		foreach ($this->teams as $team) {
			/** @var $team AgentTeam */
			$agent->addTeam($team); // bidirectional

			if ($team === $this->primary_team) {
				$found_primary = true;
			}
		}

		if (!$found_primary) {
			if ($this->teams) {
				$this->primary_team = Arrays::getFirstItem($this->teams);
			} else {
				$this->primary_team = null;
			}
		}

		#------------------------------
		# Groups
		#------------------------------

		$group_coll_helper = new CollectionHelper($agent, 'usergroups', function ($x) {
			return $x->is_agent_group;
		});
		if ($this->agent_groups instanceof ArrayCollection) {
			$this->agent_groups = $this->agent_groups->toArray();
		}
		$group_coll_helper->setCollection($this->agent_groups);

		#------------------------------
		# Email addresses
		#------------------------------

		$set_emails  = array_map(function($x) { return strtolower($x); },        $this->emails);
		$have_emails = array_map(function($y) { return strtolower($y->email); }, $agent->emails->toArray());

		$add_emails = array_diff($set_emails, $have_emails);
		$del_emails = array_diff($have_emails, $set_emails);

		foreach ($add_emails as $email_address) {
			$email = new PersonEmail();
			$email->person       = $agent;
			$email->email        = $email_address;
			$email->is_validated = true;

			$agent->addEmailAddress($email);
			$em->persist($email);
		}

		foreach ($del_emails as $email_address) {
			$email = $agent->findEmailAddress($email_address);
			if ($email) {
				$agent->removeEmailAddressId($email['id']);
				$em->remove($email);
			}
		}

		$primary_email_address = strtolower(Arrays::getFirstItem($this->emails));
		foreach ($agent->emails as $email) {
			if (strtolower($email->email) == $primary_email_address && !$agent->primary_email) {
				$agent->primary_email = $email;
				break;
			}
		}

		$em->flush();

		foreach ($this->notification_settings as $k => $v) {
			$p = $agent->setPreference('agent_notif.'.$k, (int) $v);
			$em->persist($p);
		}

		$em->persist($agent);
		$agent->primary_team = $this->primary_team;

		$em->flush();
	}


	############################################################################
	# Validation Metadata
	############################################################################

	public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('name', new Constraints\NotBlank(array(
			'message' => 'Name should not be blank.',
		)));
		$metadata->addPropertyConstraint('emails', new Constraints\All(array(
			'constraints' => array(
				new Constraints\NotBlank(),
				new Constraints\Email(),
			)
		)));

		$metadata->addPropertyConstraint('emails', new Constraints\Count(array('min' => 1, 'minMessage' => '[emails_count] At least one email address is required')));

		$metadata->addPropertyConstraint('teams', new Constraints\All(array(
			'constraints' => array(
				new DeskproConstraints\AgentTeamConstraint()
			)
		)));
		$metadata->addPropertyConstraint('agent_groups', new Constraints\All(array(
			'constraints' => array(
				new DeskproConstraints\AgentGroupConstraint()
			)
		)));
	}
}
