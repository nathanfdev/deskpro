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

use Application\DeskPRO\App;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;

/**
 * Ticket categories
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketCategory")
 * @ORM_Mapping\Table(name="ticket_categories")
 */
class TicketCategory extends \Application\DeskPRO\Domain\DomainObject implements HasPhraseName
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * @var TicketCategory
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketCategory")
	 * @ORM_Mapping\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $parent = null;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TicketCategory", mappedBy="parent")
	 * @ORM_Mapping\OrderBy({"title" = "ASC"})
	 */
	protected $children = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

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
			return new \Doctrine\Common\Collections\ArrayCollection();
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
		$phrase_name = 'obj_ticketcategory.' . $this->id . '_' . $property;

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
