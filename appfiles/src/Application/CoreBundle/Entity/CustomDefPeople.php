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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A custom field definition
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="custom_def_people")
 */
class CustomDefPeople extends CustomDefAbstract
{
	/**
	 * @var CustomDefPeople
	 * @OneToOne(targetEntity="CustomDefPeople")
	 * @JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent = null;

	/**
	 * Field children
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @OneToMany(targetEntity="CustomDefPeople", mappedBy="parent_id")
	 */
	protected $field_children = null;
}