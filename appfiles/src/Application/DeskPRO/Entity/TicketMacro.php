<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;

/**
 * Ticket macros
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketMacro")
 * @orm:Table(name="ticket_macros")
 */
class TicketMacro extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @orm:Column(name="labels", type="string", length=1000)
	 */
	protected $labels = '';

	/**
	 * @var bool
	 * @orm:Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var bool
	 * @orm:Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * @var string
	 * @orm:Column(name="actions", type="array")
	 */
	protected $actions = array();

	public function getActionsArrayDesc()
	{
		$ret = array();

		foreach ($this->actions as $info) {
			if (!isset($info['rule_type'])) continue;

			$type = $info['rule_type'];
			unset($info['rule_type']);

			if (count($info) == 1) {
				$info = array_pop($info);
			}

			$ret[$type] = $info;
		}

		return $ret;
	}


	/**
	 * Get a simple array of actions used to pass back to views to update
	 * UI.
	 *
	 * $ticket may be null, in which case no conditions are assumed.
	 *
	 * @param Entity\Ticket $ticket The context (used ex in replies for replacements)
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionsCollection
	 */
	public function getActionsCollection(Entity\Ticket $ticket = null)
	{
		$factory = new ActionsFactory();
		$collection = new ActionsCollection();

		foreach ($this->actions as $action_info) {
			$action = $factory->create($action_info['type'], $action_info);
			$collection->add($action);
		}

		return $collection;
	}



	/**
	 * Get actions for a collection of tickets.
	 *
	 * @param array $tickets
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionsCollection[]
	 */
	public function getActionsCollectionsForTickets($tickets = null)
	{
		$actions = array();

		if ($tickets) {
			foreach ($tickets as $ticket) {
				$actions[$ticket['id']] = $this->getActionsCollection($ticket);
			}
		}

		return $actions;
	}



	public function performOnTicket(Ticket $ticket, Entity\Person $person_context = null)
	{
		$collection = $this->getActionsCollection($ticket);

		if (!$person_context) {
			$person_context = App::getCurrentPerson();
		}

		$collection->apply($ticket, $person_context);
	}

	public function performOnPerson(Entity\Person $person)
	{
		$did_change = false;

		foreach ($this->actions as $action) {

			$term = $action['type'];
			$term_id = null;

			// $term of people_field[12] becomes $term=people_field, $term_id=12
			$m = null;
			if (preg_match('#^(.*?)\[(.*?)\]$#', $term, $m)) {
				$term = $m[1];
				$term_id = $m[2];
			}

			switch ($term) {
				case 'people_field':

					$value = $action;
					unset($value['rule_type'], $value['op'], $value['renderable_value']);

					$field = App::getEntityRepository('DeskPRO:CustomDefPerson')->find($term_id);
					if (!$field) {
						break;
					}

					foreach ($field->getHandler()->getDataFromForm($value) as $info) {
						$person->setCustomData($info[0], $info[1], $info[2]);
					}

					$did_change = true;
					break;

				case 'person_organization_id':
					$person['organization_id'] = $action['person_organization_id'];
					$did_change = true;
					break;
			}
		}

		if ($did_change) {
			App::getOrm()->persist($person);
		}

		return $did_change;
	}
}