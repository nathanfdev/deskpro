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
 * Departments
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Department")
 * @orm:Table(name="departments")
 */
class Department extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @var Department
	 * @orm:OneToOne(targetEntity="Department")
	 * @orm:JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent = null;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="Department", mappedBy="parent")
	 * @orm:OrderBy({"title" = "ASC"})
	 */
	protected $children = null;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	public function __construct()
	{
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
	}



	/**
	 * Get the 'full' name of this department by prepending the parents name to it.
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
	public function addChild(Department $department)
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
		// We only support a second level,
		// so if *we* are the child, then there are no more
		if ($this->parent) {
			// empty collection
			return new Doctrine\Common\Collections\ArrayCollection();
		}

		return $this->children;
	}



	/**
	 * Get all children down the entire tree
	 *
	 * Note: Currently only two levels, so this is the same as getChildren()
	 *
	 * @return array
	 */
	public function getAllChildren()
	{
		return $this->getChildren();
	}
}