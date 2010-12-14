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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A custom field definition
 *
 * @orm:MappedSuperclass
 */
class CustomDefAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * Field parent
	 *
	 * MUST BE IMPLEMENT IN CHILD CLASS
	 *
	 * @var XXX
	 * @orm:OneToOne(targetEntity="XXX")
	 * @orm:JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	//protected $parent = null;

	/**
	 * Field children
	 *
	 * MUST BE IMPLEMENT IN CHILD CLASS
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="CustomDefXXX", mappedBy="parent_id")
	 */
	//protected $children = null;

	/**
	 * The title
	 *
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title = '';

	/**
	 * The handler class.
	 *
	 * May be nullable if the def is a child representing some kind of option.
	 * For example, a select box has children who we only need the 'title' for.
	 *
	 * @var string
	 * @orm:Column(name="handler_class", type="string", length=255, nullable=true)
	 */
	protected $handler_class;

	/**
	 * Options for the field
	 *
	 * @orm:Column(name="options", type="array")
	 */
	protected $options = array();

	/**
	 * Field children
	 *
	 * MUST BE IMPLEMENT IN CHILD CLASS
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="FormField", mappedBy="parent_id")
	 */
	//protected $field_children = null;

	/**
	 * @var Application\DeskPRO\Form\FieldHandler\AbstractFieldHandler
	 */
	protected $_handler_instance = null;


	public function __construct()
	{
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
	}


	/**
	 * Get the DeskPRO form field object that knows how to render data etc.
	 *
	 * @return Application\DeskPRO\Form\FieldHandler\AbstractFieldHandler
	 */
	public function getHandler()
	{
		if ($this->_handler_instance !== null) return $this->_handler_instance;

		if ($this['handler_class'] == 'x') {
			$e = new \Exception();
			echo $e->getTraceAsString();
			exit;
		}

		$classname = $this['handler_class'];

		$this->_handler_instance = new $classname($this);

		return $this->_handler_instance;
	}

	

	/**
	 * Get an array of all IDs from this def and down.
	 *
	 * @return array
	 */
	public function getHierarchyIds()
	{
		$ids = array($this->id);
		foreach ($this->children as $child) {
			$ids = array_merge($ids, $child->getHierarchyIds());
		}

		return $ids;
	}
}