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

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Records labels on people.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="labels_people")
 */
class LabelPerson extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'people';

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;
}