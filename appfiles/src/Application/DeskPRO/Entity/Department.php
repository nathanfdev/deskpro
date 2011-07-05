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

use Application\DeskPRO\App;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;

/**
 * Departments
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Department")
 * @orm:Table(name="departments")
 */
class Department extends \Application\DeskPRO\Domain\DomainObject implements HasPhraseName
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @var Department
	 * @orm:ManyToOne(targetEntity="Department")
	 * @orm:JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
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

	/**
	 * @var int
	 * @orm:Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	public function __construct()
	{
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getParentId()
	{
		if ($this->parent) {
			return $this->parent['id'];
		}

		return 0;
	}

	public function setParentId($id)
	{
		if ($id) {
			$this->parent = App::getEntityRepository('DeskPRO:Department')->find($id);
		} else {
			$this->parent = null;
		}
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


	/**
	 * Return a unique ID that we can use to look up translations for this object
	 *
	 * @param string $property If supplied, the property on the object we want to translate.
	 * @return string
	 */
	public function getPhraseName($property = null, Translate $translate)
	{
		if (!$property) {
			$property = 'title';
		}
		$phrase_name = 'obj_department.' . $this->id . '_' . $property;
		if ($translate->getPersonContext() AND !$translate->getPersonContext()->getIsAgent()) {
			$phrase_name .= "_user";
		}

		return $phrase_name;
	}


	/**
	 * Get the default value phrase for the object
	 *
	 * @param string $property If supplied, the property on the object we want to translate.
	 * @return string
	 */
	public function getPhraseDefault($property = null, Translate $translate)
	{
		return $this->title;
	}
}