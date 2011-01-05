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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

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
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
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
	protected $actions;


	public function performOnTicket(Ticket $ticket)
	{
		foreach ($this->actions as $action) {
			switch ($action['rule_type']) {
				case 'department':
					$ticket['department_id'] = $action['department'];
					break;

				case 'category':
					$ticket['category_id'] = $action['category'];
					break;

				case 'agent':

					// -1 means "current user" -- used for generic shared macros
					if ($action['agent'] == -1) {
						$agent = App::getCurrentPerson();
						if ($agent) {
							$action['agent'] = $agent['id'];
						} else {
							return;// todo err?
						}
					}
					$ticket['agent_id'] = $action['agent'];
					break;

				case 'product':
					$ticket['product_id'] = $action['product'];
					break;
				
				case 'priority':
					$ticket['priority_id'] = $action['priority'];
					break;

				case 'reply':
					$agent = App::getCurrentPerson();

					if (!$agent) {
						return;
						//todo err?
					}

					$message = new Entity\TicketMessage();
					$message['person']  = $agent;
					$message['ticket']  = $ticket;
					$message['message'] = $action['reply'];

					$ticket->addMessage($message);

					App::getOrm()->persist($message);

					break;
			}
		}

		App::getOrm()->persist($ticket);
	}
}