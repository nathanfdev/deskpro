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
 * A PersonField describes a type of field that is set on a Person, and how the data
 * is to be treated/inputted/transformed etc.
 *
 * @Entity(repositoryClass="Application\CoreBundle\EntityRepository\PersonField")
 * @HasLifecycleCallbacks
 * @Table(name="person_fields")
 */
class PersonField extends FormField
{
	/**
	 * The parent ID for multi-field fields.
	 *
	 * @var int
	 * @Column(name="parent_id", type="integer", nullable=true)
	 */
	protected $parent_id = null;
	
	/**
	 * Field parent
	 *
	 * @var PersonField
	 * @OneToOne(targetEntity="PersonField")
	 * @JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent = null;

	/**
	 * Field children
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @OneToMany(targetEntity="PersonField", mappedBy="parent")
	 */
	protected $field_children = null;
}