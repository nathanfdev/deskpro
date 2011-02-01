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
 * Ticket categories
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketCategory")
 * @orm:Table(name="ticket_categories")
 */
class TicketCategory extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var TicketCategory
	 * @orm:OneToOne(targetEntity="TicketCategory")
	 * @orm:JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent = null;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TicketCategory", mappedBy="parent")
	 * @orm:OrderBy({"title" = "ASC"})
	 */
	protected $children = null;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 * @orm:ManyToOne(targetEntity="Department")
	 * @orm:JoinColumn(name="department_id", referencedColumnName="id")
	 */
	protected $department = null;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var int
	 * @orm:Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	public function __construct()
	{
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
	}


	/**
	 * Get the 'full' name
	 *
	 * @return string
	 */
	public function getFullTitle($sep = null)
	{
		if ($sep === null) $sep = ' > ';

		if (!$this->parent) {
			return $this->title;
		}

		return $this->parent['title'] . $sep . $this->title;
	}


	/**
	 * Add a child department
	 * @param Department $department
	 */
	public function addChild(TicketCategory $department)
	{
		$department['parent'] = $this;
		$this->children->add($department);
	}


	/**
	 * Get children
	 * @return Doctrine\Common\Collections\ArrayCollection
	 */
	public function getChildren()
	{
		if ($this->parent) {
			// empty collection
			return new Doctrine\Common\Collections\ArrayCollection();
		}

		return $this->children;
	}



	/**
	 * Get all children down the entire tree
	 *
	 * @return array
	 */
	public function getAllChildren()
	{
		return $this->getChildren();
	}
}