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
use Gedmo\Mapping\Annotation as Gedmo_Mapping;

use Application\DeskPRO\App;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;

use DoctrineExtensions\NestedSet\Node;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Basic hierarchicial category entity. Hierarchy is maintained automatically
 * by a Doctrine NestedSet implementation
 *
 * @Gedmo_Mapping\Tree(type="nested")
 * @ORM_Mapping\MappedSuperclass
 */
class CategoryAbstract extends \Application\DeskPRO\Domain\DomainObject implements HasPhraseName
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

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

	// IMPLEMENT IN CHILDREN : Limitation of doctrine mapping, you have to map these with the correct targets
	///**
	// * @Gedmo_Mapping\TreeParent
	// * @ORM_Mapping\ManyToOne(targetEntity="CategoryAbstract", inversedBy="children")
	// */
	//protected $parent;
	//
	///**
	// * @ORM_Mapping\OneToMany(targetEntity="CategoryAbstract", mappedBy="parent")
	// * @ORM_Mapping\OrderBy({"lft" = "ASC"})
	// */
	//protected $children;

	/**
	 * @Gedmo_Mapping\TreeRoot
	 * @ORM_Mapping\Column(name="root", type="integer", nullable=true)
	 */
	protected $root;

	/**
	 * @Gedmo_Mapping\TreeLevel
	 * @ORM_Mapping\Column(name="depth", type="integer")
	 */
	protected $depth;

	/**
	 * @Gedmo_Mapping\TreeLeft
	 * @ORM_Mapping\Column(name="lft", type="integer")
	 */
	protected $lft;

	/**
	 * @Gedmo_Mapping\TreeRight
	 * @ORM_Mapping\Column(name="rgt", type="integer")
	 */
	protected $rgt;

	/**
	 * Local cache of some structure info with this category
	 * @var array()
	 */
	protected $_structure = array();


	/**
	 * Get an array of titles from parents down to this.
	 *
	 * @return array
	 */
	public function getTitleParts()
	{
		$titles = array();
		foreach ($this->getTreeParents() as $p) {
			$titles[] = $p['title'];
		}

		return $titles;
	}


	/**
	 * Get the full display title for the category with all parents parts, separated
	 * by $sep. Example: Category > Subcategory
	 *
	 * @param string $sep
	 * @return string
	 */
	public function getFullTitle($sep = ' > ')
	{
		return implode(' > ', $this->getTitleParts());
	}

	
	/**
	 * Gets all parents in the tree, in order (left to right, aka, top to bottom)
	 *
	 * @return array
	 */
	public function getTreeParents()
	{
		if (isset($this->_structure['all_parents'])) return $this->_structure['all_parents'];

		$this->_structure['all_parents'] = App::getEntityRepository(get_class($this))->getPath($this);

		return $this->_structure['all_parents'];
	}


	
	/**
	 * Get all IDs of this tree, from this node and downwards.
	 *
	 * @param  $including_this Include this nodes ID in the array of ids
	 * @return void
	 */
	public function getTreeIds($including_this = true)
	{
		if (!isset($this->_structure['all_child_ids'])) {
			$ids = App::getEntityRepository(get_class($this))->childrenIds($this);
			$this->_structure['all_child_ids'] = $ids;
		}

		$ids = $this->_structure['all_child_ids'];
		if ($including_this) {
			array_unshift($ids, $this->id);
		}

		return $ids;
	}



	public function getUrlSlug()
	{
		return $this->id . '-' . Strings::slugifyTitle($this->title);
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
		$name = strtolower(Util::getBaseClassname($this));
		$phrase_name = 'obj_'.$name.'.' . $this->id . '_' . $property;

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