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

use \Application\DeskPRO\App;

use DoctrineExtensions\NestedSet\Node;
use Orb\Util\Strings;

/**
 * Basic hierarchicial category entity. Hierarchy is maintained automatically
 * by a Doctrine NestedSet implementation
 *
 * @gedmo:Tree(type="nested")
 * @orm:MappedSuperclass
 */
class CategoryAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

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

	// IMPLEMENT IN CHILDREN : Limitation of doctrine mapping, you have to map these with the correct targets
	///**
	// * @gedmo:TreeParent
	// * @orm:ManyToOne(targetEntity="CategoryAbstract", inversedBy="children")
	// */
	//protected $parent;
	//
	///**
	// * @orm:OneToMany(targetEntity="CategoryAbstract", mappedBy="parent")
	// * @orm:OrderBy({"lft" = "ASC"})
	// */
	//protected $children;

	/**
	 * @gedmo:TreeRoot
	 * @orm:Column(name="root", type="integer", nullable=true)
	 */
	protected $root = 0;

	/**
	 * @gedmo:TreeLevel
	 * @orm:Column(name="depth", type="integer")
	 */
	protected $depth;

	/**
	 * @gedmo:TreeLeft
	 * @orm:Column(name="lft", type="integer")
	 */
	protected $lft;

	/**
	 * @gedmo:TreeRight
	 * @orm:Column(name="rgt", type="integer")
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

		$titles[] = $this->title;

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
}