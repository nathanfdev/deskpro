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

/**
 * Person log items (aka user stream)
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\PersonLog")
 * @orm:Table(name="person_logs")
 */
class PersonLog extends \Application\DeskPRO\Domain\DomainObject
{
	const TYPE_TICKET_NEW                   = 'ticket_new';
	const TYPE_TICKET_REPLY                 = 'ticket_reply';
	const TYPE_TICKET_CLOSED                = 'ticket_closed';
	const TYPE_PERSON_UPDATED               = 'person_changed_profile';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @orm:Column(name="action_type", type="string", length=255)
	 */
	protected $action_type;

	/**
	 * @var string
	 * @orm:Column(name="details", type="array")
	 */
	protected $details = array();

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
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
}