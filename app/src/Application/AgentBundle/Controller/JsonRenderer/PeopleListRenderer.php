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
 */

namespace Application\AgentBundle\Controller\JsonRenderer;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PeopleResultsDisplay;
use Application\DeskPRO\Tickets\TicketResultsDisplay;
use Application\DeskPRO\Util;
use Orb\Util\Arrays;

class PeopleListRenderer
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $db;

	/**
	 * @var \Application\DeskPRO\Entity\Organization[]
	 */
	private $cache_orgs;


	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
		$this->em = $container->getEm();
		$this->db = $container->getDb();
	}


	/**
	 * @param PeopleResultsDisplay $display
	 * @param null $fn_visitor
	 * @return array
	 */
	public function renderArray(PeopleResultsDisplay $display, $fn_visitor = null)
	{
		if (!$display->getCount()) {
			return array();
		}

		#------------------------------
		# Precache data
		#------------------------------

		$org_ids    = array();
		$person_ids = array();

		foreach ($display->getPeople() as $person) {
			$person_ids[] = $person->id;
			if ($person->organization) {
				$org_ids[] = $person->organization->id;
			}
		}

		if ($org_ids) {
			$this->cache_orgs = $this->em->getRepository('DeskPRO:Organization')->getByIds($org_ids);
			$this->cache_orgs = Arrays::keyFromData($this->cache_orgs, 'id');
		}

		#------------------------------
		# Generate data array
		#------------------------------

		$json_array = array();

		foreach ($display->getPeople() as $person) {
			$data = $this->renderPerson($person, $display);

			if ($fn_visitor) {
				$data = call_user_func($fn_visitor, $person, $data);
			}

			$json_array[] = $data;
		}

		return $json_array;
	}


	/**
	 * @param PeopleResultsDisplay $display
	 * @return string
	 */
	public function renderJson(PeopleResultsDisplay $display)
	{
		if (!$display->getCount()) {
			return '[]';
		}
		return Util::jsonEncode($this->renderArray($display));
	}

	private function renderPerson(Person $person, PeopleResultsDisplay $display)
	{
		$data = array();

		$data['id']                    = $person->id;
		$data['is_contact']            = $person->is_contact;
		$data['is_user']               = $person->is_user;
		$data['is_agent']              = $person->is_agent;
		$data['was_agent']             = $person->was_agent;
		$data['can_agent']             = $person->can_agent;
		$data['can_admin']             = $person->can_admin;
		$data['is_confirmed']          = $person->is_confirmed;
		$data['is_agent_confirmed']    = $person->is_agent_confirmed;
		$data['is_deleted']            = $person->is_deleted;
		$data['is_disabled']           = $person->is_disabled;
		$data['creation_system']       = $person->creation_system;
		$data['name']                  = $person->name;
		$data['first_name']            = $person->first_name;
		$data['last_name']             = $person->last_name;
		$data['title_prefix']          = $person->title_prefix;
		$data['override_display_name'] = $person->override_display_name;
		$data['summary']               = $person->summary;
		$data['organization_position'] = $person->organization_position;
		$data['organization_manager']  = $person->organization_manager;
		$data['timezone']              = $person->timezone;

		$data['date_created'] = $person->date_created->format('Y-m-d H:i:s');
		$data['date_created_ts'] = $person->date_created->getTimestamp();

		$data['display_name']  = $person->getDisplayName();
		if ($person->primary_email) {
			$data['primary_email'] = array(
				'id'    => $person->primary_email->id,
				'email' => $person->primary_email->email
			);
		}

		$data['picture_url']    = $person->getPictureUrl();
		$data['picture_url_80'] = $person->getPictureUrl(80);
		$data['picture_url_64'] = $person->getPictureUrl(64);
		$data['picture_url_50'] = $person->getPictureUrl(50);
		$data['picture_url_45'] = $person->getPictureUrl(45);
		$data['picture_url_32'] = $person->getPictureUrl(32);
		$data['picture_url_22'] = $person->getPictureUrl(22);
		$data['picture_url_16'] = $person->getPictureUrl(16);


		$custom_data = $display->getUserFieldData($person);
		if ($custom_data) {
			$field_manager = $this->container->getSystemService('person_fields_manager');

			$rendered_data = $field_manager->getRenderedToText($field_manager->createFieldDataFromArray($custom_data));
			foreach ($rendered_data as $fid => $v) {
				$data["field{$fid}"] = $v['rendered'];
			}
		}

		return $data;
	}
}
