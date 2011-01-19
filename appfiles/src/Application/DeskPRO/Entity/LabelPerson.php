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
 * Records labels on people.
 *
 * @orm:Entity
 * @orm:Table(name="labels_people")
 */
class LabelPerson extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @orm:Id
	 * @orm:Column(name="label", type="string", length=255)
	 */
	protected $label;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;
}