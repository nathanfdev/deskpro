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
use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Ticket log items
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketLog")
 * @ORM_Mapping\Table(name="tickets_logs")
 */
class TicketLog extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @ORM_Mapping\ManyToOne(targetEntity="Ticket")
	 * @ORM_Mapping\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="action_type", type="string", length=40)
	 */
	protected $action_type;

	/**
	 * If the log involves a specific thing in a ticket (eg a message that was moved),
	 * then that is this id.
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="id_object", type="integer", nullable=true)
	 *
	 */
	protected $id_object = null;

	/**
	 * The ID of the previous entity changed, or any other numeric value.
	 * @var int
	 * @ORM_Mapping\Column(name="id_before", type="integer", nullable=true)
	 *
	 */
	protected $id_before = null;

	/**
	 * The ID of the new entity, or any other numeric value.
	 * @var int
	 * @ORM_Mapping\Column(name="id_after", type="integer", nullable=true)
	 *
	 */
	protected $id_after = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="details", type="array")
	 */
	protected $details = array();

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	public function getPersonId()
	{
		return $this->person['id'];
	}

	public function setPersonId($id)
	{
		$person = App::getOrm()->getRepository('DeskPRO:Person')->find($id);
		$this['person'] = $person;
	}

	public function getTicketId()
	{
		return $this->ticket['id'];
	}

	public function setTicketId($id)
	{
		$ticket = App::getOrm()->getRepository('DeskPRO:Ticket')->find($id);
		$this['ticket'] = $ticket;
	}

	public function setDetails(array $details)
	{
		if (isset($details['id_before'])) {
			$this['id_before'] = $details['id_before'];
			unset($details['id_before']);
		}
		if (isset($details['id_after'])) {
			$this['id_after'] = $details['id_after'];
			unset($details['id_after']);
		}
		if (isset($details['id_object'])) {
			$this['id_object'] = $details['id_object'];
			unset($details['id_object']);
		}

		$this->setModelField('details', $details);
	}
}
