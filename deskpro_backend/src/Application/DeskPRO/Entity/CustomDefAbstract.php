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

use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A custom field definition
 *
 * @ORM_Mapping\MappedSuperclass
 */
class CustomDefAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * Is the field associated with a plugin?
	 * These generally cant be edited.
	 *
	 * @var \Application\DeskPRO\Entity\Plugin
	 * @ORM_Mapping\OneToOne(targetEntity="Plugin")
	 * @ORM_Mapping\JoinColumn(name="plugin_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $plugin = null;

	/**
	 * JS class to init
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="js_class", type="string", length=255)
	 */
	protected $js_class = '';

	/**
	 * True if this field uses a custom template when rendering the form input
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="has_form_template", type="boolean")
	 */
	protected $has_form_template = false;

	/**
	 * True i this field uses a custom template when rendering the form value for display
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="has_display_template", type="boolean")
	 */
	protected $has_display_template = false;

	/**
	 * Field parent
	 *
	 * MUST BE IMPLEMENT IN CHILD CLASS
	 *
	 * @var XXX
	 * @ORM_Mapping\OneToOne(targetEntity="XXX")
	 * @ORM_Mapping\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
	 */
	//protected $parent = null;

	/**
	 * Field children
	 *
	 * MUST BE IMPLEMENT IN CHILD CLASS
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="CustomDefXXX", mappedBy="parent_id", cascade={"persist", "remove", "merge"})
	 * @ORM_Mapping\OrderBy({"display_order" = "ASC"})
	 */
	//protected $children = null;

	/**
	 * The title
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title = '';

	/**
	 * The handler class.
	 *
	 * May be nullable if the def is a child representing some kind of option.
	 * For example, a select box has children who we only need the 'title' for.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="handler_class", type="string", length=255, nullable=true)
	 */
	protected $handler_class = null;

	/**
	 * Options for the field
	 *
	 * @ORM_Mapping\Column(name="options", type="array")
	 */
	protected $options = array();

	/**
	 * Can the field be viewed by the user?
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="is_user_enabled", type="boolean")
	 */
	protected $is_user_enabled = true;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	/**
	 * @var Application\DeskPRO\Form\FieldHandler\AbstractFieldHandler
	 */
	protected $_handler_instance = null;


	public function __construct()
	{
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
	}



	/**
	 * Add a child to this field
	 *
	 * @param CustomDefAbstract $def
	 */
	public function addChild(CustomDefAbstract $def)
	{
		$this->children->add($def);
		$def['parent'] = $this;
	}



	/**
	 * Remove a child field
	 *
	 * @param CustomDefAbstract $def
	 */
	public function removeChild(CustomDefAbstract $def)
	{
		$this->children->removeElement($def);
	}



	/**
	 * Remove a child based on the childs field id
	 *
	 * @param int $def_id
	 */
	public function removeChildId($def_id)
	{
		foreach ($this->children as $k => $v) {
			if ($v['id'] == $def_id) {
				$this->children->remove($k);
				return;
			}
		}
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

		// TODO: Maybe something to do with admin saving the name and magic quotes
		// Look into that, then remove this line because it shouldnt be needed
		$classname = preg_replace('#\\\\+#', '\\', $classname);

		$this->_handler_instance = new $classname($this);

		return $this->_handler_instance;
	}



	/**
	 * Get an array of all IDs from this def and down.
	 *
	 * @return array
	 */
	public function getAllChildIds()
	{
		$ids = array($this->id);
		foreach ($this->children as $child) {
			$ids = array_merge($ids, $child->getAllChildIds());
		}

		return $ids;
	}



	/**
	 * Creates a new instance of the same type and sets its parent to this object.
	 * Note that you should still add it to the tree with addField.
	 *
	 * @return CustomDefAbstract
	 */
	public function createChild()
	{
		$obj = new static();
		$obj['parent'] = $this;

		return $obj;
	}



	/**
	 * Get the value of an option, or a default value if none is set.
	 *
	 * @param  $name
	 * @param null $default
	 * @return array|null
	 */
	public function getOption($name, $default = null)
	{
		if (!isset($this->options[$name])) {
			return $default;
		}

		return $this->options[$name];
	}


	/**
	 * Set a value of an option
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setOption($name, $value)
	{
		if ($value === null) {
			unset($this->options[$name]);
		} else {
			$this->options[$name] = $value;
		}
	}


	/**
	 * Get the phrasename for the handler class. This is just
	 * the key of the phrase when showing this fields type.
	 * For example, for phrases like "Text box" or "Checkbox" etc listed in the admin interface.
	 *
	 * @return string
	 */
	public function getHandlerClassPhrase()
	{
		$phrase = $this->handler_class;
		$phrase = str_replace('Application\\DeskPRO\\CustomFields\\Handler\\', '', $phrase);
		$phrase = str_replace('\\', '_', $phrase);
		$phrase = "agent.field_type_$phrase";
		$phrase = strtolower($phrase);

		return $phrase;
	}


	/**
	 * The "short name" for the handler type.
	 *
	 * @return string
	 */
	public function getTypeName()
	{
		$name = $this->handler_class;
		$name = str_replace('Application\\DeskPRO\\CustomFields\\Handler\\', '', $name);
		$name = str_replace('\\', '_', $name);
		$name = strtolower($name);

		return $name;
	}


	/**
	 * Fetch the search capabiltiies supported by the field.
	 *
	 * @return array
	 */
	public function getSearchCapabilities()
	{
		return $this->getHandler()->getSearchCapabilities();
	}

	/**
	 * Get filter capabilties supported by the field.
	 *
	 * @return array
	 */
	public function getFilterCapabilities()
	{
		return $this->getHandler()->getFilterCapabilities();
	}

	public function getParentId()
	{
		return $this->parent['id'];
	}
}
