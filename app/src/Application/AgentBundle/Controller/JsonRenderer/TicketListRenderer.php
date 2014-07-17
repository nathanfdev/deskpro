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
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketResultsDisplay;
use Application\DeskPRO\Util;
use Orb\Util\Arrays;

class TicketListRenderer
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

	/**
	 * @var \Application\DeskPRO\Tickets\TicketResultsDisplay
	 */
	private $ticket_display;


	/**
	 * @param TicketResultsDisplay $ticket_display
	 */
	public function __construct(TicketResultsDisplay $ticket_display)
	{
		$this->ticket_display = $ticket_display;
		$this->container = App::getContainer();
		$this->em = App::getContainer()->getEm();
		$this->db = App::getContainer()->getDb();
	}


	/**
	 * @param null|callback $fn_visitor
	 * @return array
	 */
	public function renderTicketDisplayArray($fn_visitor = null)
	{
		if (!$this->ticket_display->getCount()) {
			return array();
		}

		#------------------------------
		# Precache data
		#------------------------------

		$org_ids    = array();
		$person_ids = array();
		$ticket_ids = array();

		foreach ($this->ticket_display->getTickets() as $ticket) {
			$ticket_ids[] = $ticket->id;
			$person_ids[] = $ticket->person->id;
			if ($ticket->organization) {
				$org_ids[] = $ticket->organization->id;
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

		foreach ($this->ticket_display->getTickets() as $ticket) {
			$data = $this->renderTicket($ticket);

			if ($fn_visitor) {
				$data = call_user_func($fn_visitor, $ticket, $data);
			}

			$json_array[] = $data;
		}

		return $json_array;
	}


	/**
	 * @return string
	 */
	public function renderTicketDisplayJson()
	{
		if (!$this->ticket_display->getCount()) {
			return '[]';
		}
		return Util::jsonEncode($this->renderTicketDisplayArray());
	}


	/**
	 * @param Ticket $ticket
	 * @return array
	 */
	private function renderTicket(Ticket $ticket)
	{
		$data = array();

		$data['id']                      = $ticket->id;
		$data['ref']                     = $ticket->ref;
		$data['auth']                    = $ticket->auth;
		$data['sent_to_address']         = $ticket->sent_to_address;
		$data['creation_system']         = $ticket->creation_system;
		$data['creation_system_option']  = $ticket->creation_system_option;
		$data['ticket_hash']             = $ticket->ticket_hash;
		$data['status']                  = $ticket->status;
		$data['hidden_status']           = $ticket->hidden_status;
		$data['validating']              = $ticket->validating;
		$data['is_hold']                 = $ticket->is_hold;
		$data['urgency']                 = $ticket->urgency;
		$data['count_agent_replies']     = $ticket->count_agent_replies;
		$data['count_user_replies']      = $ticket->count_user_replies;
		$data['feedback_rating']         = $ticket->feedback_rating;

		foreach (array('date_feedback_rating', 'date_created', 'date_resolved', 'date_archived', 'date_first_agent_assign', 'date_first_agent_reply', 'date_last_agent_reply', 'date_last_user_reply', 'date_agent_waiting', 'date_user_waiting', 'date_status', 'date_locked') as $field) {
			if ($ticket->$field) {
				$data[$field] = $ticket->$field->format('Y-m-d H:i:s');
				$data["{$field}_ts"] = $ticket->$field->getTimestamp();
			}
		}

		$data['total_user_waiting']      = $ticket->total_user_waiting;
		$data['total_to_first_reply']    = $ticket->total_to_first_reply;
		$data['subject']                 = $ticket->subject;
		$data['properties']              = $ticket->properties;
		$data['worst_sla_status']        = $ticket->worst_sla_status;
		$data['waiting_times']           = $ticket->waiting_times;

		foreach (array('language', 'department', 'category', 'priority', 'workflow', 'product', 'person', 'agent', 'agent_team', 'organization') as $field) {
			$data[$field] = null;

			if (!$ticket->$field) {
				continue;
			}

			switch ($field) {
				case 'langauge':
					$lang = $this->container->getLanguageData()->get($ticket->language->getId());
					if ($lang) {
						$data['language'] = array('id' => $lang->id, 'title' => $lang->title);
					}
					break;

				case 'department':
					$dep = $this->container->getDataService('Department')->get($ticket->department->getId());
					if ($dep) {
						$data['department'] = array('id' => $dep->id, 'title' => $dep->title, 'title_full' => $dep->getFullTitle());
					}
					break;

				case 'organization':
					if (isset($this->cache_orgs[$ticket->organization->getId()])) {
						$data['organization'] = array(
							'id' => $this->cache_orgs[$ticket->organization->getId()]->id,
							'name' => $this->cache_orgs[$ticket->organization->getId()]->name
						);
					}
					break;

				case 'person':
					$data['person'] = $this->renderPerson($this->ticket_display->getPerson($ticket));
					break;

				case 'agent':
					$data['agent'] = $this->container->getAgentData()->has($ticket->agent->getId()) ? $this->renderPerson($this->container->getAgentData()->get($ticket->agent->getId())) : null;
					break;

				default:
					$data[$field] = $ticket->$field->toApiData(false, false);
			}
		}

		$data['labels'] = $this->ticket_display->getTicketLabels($ticket);

		$custom_data = $this->ticket_display->getTicketFieldData($ticket);
		if ($custom_data) {
			$field_manager = $this->container->getSystemService('ticket_fields_manager');

			$rendered_data = $field_manager->getRenderedToText($field_manager->createFieldDataFromArray($custom_data));
			foreach ($rendered_data as $fid => $v) {
				$data["field{$fid}"] = $v['rendered'];
			}
		}

		$data['ticket_slas'] = $ticket->ticket_slas;


		$data['previews'] = array();
		foreach ($this->ticket_display->getTicketPreview($ticket) as $m) {
			$data['previews'][] = array(
				'message' => array(
					'id'               => $m['id'],
					'preview_text'     => $m['preview_text'],
					'date_created'     => $m['date_created']->format('Y-m-d H:i:s'),
					'date_created_ts'  => $m['date_created']->getTimestamp(),
				),
				'person' => array(
					'id'            => $m['person_id'],
					'display_name'  => $m['display_name'],
					'is_agent'      => $m['is_agent'],
				),
			);
		}

		$data['flag'] = $this->ticket_display->getFlaggedColor($ticket);

		return $data;
	}

	private function renderPerson(Person $person)
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

		$custom_data = $this->ticket_display->getUserFieldData($person);
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
