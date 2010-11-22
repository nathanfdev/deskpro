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
 * Custom ticket data
 * 
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="custom_data_person")
 */
abstract class CustomDataPerson extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\CoreBundle\Entity\CustomDefPerson
	 * @ManyToOne(targetEntity="CustomDefTicket")
	 * @JoinColumn(name="field_id", referencedColumnName="id")
	 */
	protected $field = null;

	/**
	 * @var int
	 * @Id @Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * @var \Application\CoreBundle\Entity\Person
	 * @ManyToOne(targetEntity="Person")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;
}