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
 * Records labels on people.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="labels_people")
 */
class LabelPerson extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @Id
	 * @Column(name="label", type="string", length=255)
	 */
	protected $label;

	/**
	 * @var int
	 * @Id
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