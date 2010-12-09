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
 * A persons contact data
 *
 * @orm:Entity
 * @orm:Table(name="people_contact_data")
 */
class PersonContactData extends ContactDataAbstract
{
	/**
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;
}