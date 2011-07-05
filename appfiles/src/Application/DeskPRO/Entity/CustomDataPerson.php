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
 * Custom ticket data
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="custom_data_person",
 *     indexes={
 *         @orm:Index(name="field_id_idx", columns={"field_id","person_id"})
 * })
 */
class CustomDataPerson extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 * @orm:Id
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Entity\CustomDefPerson
	 * @orm:ManyToOne(targetEntity="CustomDefTicket", fetch="EAGER")
	 * @orm:JoinColumn(name="field_id", referencedColumnName="id", onDelete="cascade")
	 * @orm:Id
	 */
	protected $field = null;

	public function getPersonId()
	{
		return $this->person['id'];
	}
}