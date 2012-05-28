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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Application\DeskPRO\App;
use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A custom field definition
 *
 */
class CustomDefAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * Is the field associated with a plugin?
	 * These generally cant be edited.
	 *
	 * @var \Application\DeskPRO\Entity\Plugin
	 */
	protected $plugin = null;

	/**
	 * JS class to init
	 *
	 * @var string
	 */
	protected $js_class = '';

	/**
	 * True if this field uses a custom template when rendering the form input
	 *
	 * @var string
	 */
	protected $has_form_template = false;

	/**
	 * True i this field uses a custom template when rendering the form value for display
	 *
	 * @var string
	 */
	protected $has_display_template = false;

	/**
	 * Field parent
	 *
	 * MUST BE IMPLEMENT IN CHILD CLASS
	 *
	 * @var XXX
	 */
	//protected $parent = null;

	/**
	 * Field children
	 *
	 * MUST BE IMPLEMENT IN CHILD CLASS
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	//protected $children = null;

	/**
	 * The title
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * The description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * The handler class.
	 *
	 * May be nullable if the def is a child representing some kind of option.
	 * For example, a select box has children who we only need the 'title' for.
	 *
	 * @var string
	 */
	protected $handler_class = null;

	/**
	 * Options for the field
	 *
	 */
	protected $options = array();

	/**
	 * Can the field be viewed by the user?
	 *
	 * @var string
	 */
	protected $is_user_enabled = true;

	/**
	 * @var string
	 */
	protected $is_enabled = true;

	/**
	 * @var int
	 */
	protected $display_order = 0;

	/**
	 * @var string
	 */
	protected $default_value = null;

	/**
	 * @var \Application\DeskPRO\CustomFields\Handler\HandlerAbstract
	 */
	protected $_handler_instance = null;

	/**
	 * @var \Application\DeskPRO\CustomFields\FieldManager
	 */
	public $field_manager = null;


	public function __construct()
	{
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getId()
	{
		return $this->id;
	}

	public function getParentId()
	{
		if ($this->parent) {
			return $this->parent->getId();
		}

		return 0;
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
	 * @return \Application\DeskPRO\CustomFields\Handler\HandlerAbstract
	 */
	public function getHandler()
	{
		if ($this->_handler_instance !== null) return $this->_handler_instance;

		if ($this['handler_class'] == 'x') {
			$e = new \Exception();
			echo $e->getTraceAsString();
			exit;
		}

		if (!$this->handler_class) {
			throw new \InvalidArgumentException("Invalid handler class: {$this->handler_class} on {$this->id}");
		}

		$classname = $this->handler_class;
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
		$phrase = "agent.general.field_type_$phrase";
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


	/**
	 * True if this field is an actual form field (aka not a display field without any input controls).
	 *
	 * @return bool
	 */
	public function isFormField()
	{
		return $this->handler_class != 'Application\DeskPRO\CustomFields\Handler\Display';
	}


	/**
	 * @return bool
	 */
	public function isChoiceType()
	{
		switch ($this->handler_class) {
			case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':
			case 'Application\\DeskPRO\\CustomFields\\Handler\\ChoiceMultie':
				return true;

			default:
				return false;
		}
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->isMappedSuperclass = true;
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setPrimaryTable(array( 'name' => 'CustomDefAbstract', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
	}
}
