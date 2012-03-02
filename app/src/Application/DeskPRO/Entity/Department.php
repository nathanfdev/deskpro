<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;

/**
 * Departments
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Department")
 * @ORM_Mapping\Table(name="departments")
 */
class Department extends \Application\DeskPRO\Domain\DomainObject implements HasPhraseName
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id;

	/**
	 * @var Department
	 * @ORM_Mapping\ManyToOne(targetEntity="Department")
	 * @ORM_Mapping\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $parent = null;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="Department", mappedBy="parent")
	 * @ORM_Mapping\OrderBy({"title" = "ASC"})
	 */
	protected $children = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_tickets_enabled", type="boolean")
	 */
	protected $is_tickets_enabled = true;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_chat_enabled", type="boolean")
	 */
	protected $is_chat_enabled = true;

	protected $_usergroups = null;
	protected $_people = null;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="display_order", type="integer")
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
			return new \Doctrine\Common\Collections\ArrayCollection();
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
		if ($property == 'full') {
			return $this->getFullTitle();
		}
		return $this->title;
	}


	public function __toString()
	{
		return $this->getFullTitle();
	}
}
