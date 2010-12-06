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

namespace Application\DeskPRO\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A custom field definition
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="custom_def_people")
 */
class CustomDefPeople extends CustomDefAbstract
{
	/**
	 * @var CustomDefPeople
	 * @orm:OneToOne(targetEntity="CustomDefPeople")
	 * @orm:JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent = null;

	/**
	 * Field children
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="CustomDefPeople", mappedBy="parent_id")
	 */
	protected $field_children = null;
}