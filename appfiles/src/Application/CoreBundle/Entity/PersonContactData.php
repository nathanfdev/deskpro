<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;

/**
 * A persons contact data
 *
 * @Entity
 * @Table(name="people_contact_data")
 */
class PersonContactData extends ContactDataAbstract
{
	/**
	 * @var int
	 * @Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * @var \Application\CoreBundle\Entity\Person
	 * @ManyToOne(targetEntity="Person")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;
}