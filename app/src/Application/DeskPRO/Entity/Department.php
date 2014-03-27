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

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

/**
 * Departments
 *
 * @property string title
 * @property string $user_title
 * @property boolean $is_tickets_enabled
 * @property boolean $is_chat_enabled
 * @property int $display_order
 * @property Department $parent
 * @property Department $children
 *
 */
class Department extends DomainObject implements HasPhraseName
{
	/**
	 * @var int
	 *
	 */

	protected $id;

	/**
	 * @var Department
	 */

	protected $parent = null;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */

	protected $children = null;

	/**
	 * @var string
	 */

	protected $title;

	/**
	 * @var string
	 */

	protected $user_title = '';

	/**
	 * @var bool
	 */

	protected $is_tickets_enabled = true;

	/**
	 * @var bool
	 */
	protected $is_chat_enabled = true;

	protected $_usergroups = null;
	protected $_people = null;

	/**
	 * @var int
	 */
	protected $display_order = 0;

	/**
	 * @return Department
	 */

	public static function createTicketDepartment()
	{
		$dep                     = new self();
		$dep->is_tickets_enabled = true;
		$dep->is_chat_enabled    = false;

		return $dep;
	}

	/**
	 * @return Department
	 */

	public static function createChatDepartment()
	{
		$dep                     = new self();
		$dep->is_tickets_enabled = false;
		$dep->is_chat_enabled    = true;

		return $dep;
	}

	/**
	 *
	 */

	public function __construct()
	{
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * @return int
	 */

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */

	public function isType($type)
	{
		if ($type == 'tickets' && $this->is_tickets_enabled) {

			return true;

		} elseif ($type == 'chat' && $this->is_chat_enabled) {

			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */

	public function getRealUserTitle()
	{
		return $this->user_title;
	}

	/**
	 * @return string
	 */

	public function getUserTitle()
	{
		if ($this->user_title) {

			return $this->user_title;
		}

		return $this->title;
	}

	/**
	 * @param $title
	 */

	public function setUserTitle($title)
	{
		if (!$title) {

			$title = '';
		}

		$old              = $this->getRealUserTitle();
		$this->user_title = $title;

		if ($title == $old) {

			return;
		}

		$this->_onPropertyChanged('user_title', $old, $title);
	}

	/**
	 * @return Department|null
	 */

	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @return int
	 */

	public function getParentId()
	{
		if ($this->parent) {

			return $this->parent->getId();
		}

		return 0;
	}

	/**
	 * @param $id
	 */

	public function setParentId($id)
	{
		if ($id) {

			$this->parent = App::getEntityRepository('DeskPRO:Department')->find($id);

		} else {

			$this->parent = null;
		}
	}

	/**
	 * @return string
	 */

	public function getTitle()
	{
		return $this->title;

		//TODO: getX should always be the actual values
		return App::getTranslator()->getPhraseObject($this, 'title');
	}

	/**
	 * @return string
	 */

	public function getRealTitle()
	{
		return $this->title;
	}

	/**
	 * @param $title
	 */

	public function setRealTitle($title)
	{
		$this->title = $title;
	}


	/**
	 * Get the 'full' name of this department by prepending the parents name to it.
	 *
	 * @param string $sep
	 *
	 * @return string
	 */

	public function getFullTitle($sep = null)
	{
		if ($sep === null) $sep = ' > ';

		if (!$this->parent) {

			return $this->getTitle();
		}

		return $this->parent->getTitle() . $sep . $this->getTitle();
	}

	/**
	 * @param null $sep
	 *
	 * @return string
	 */

	public function getFullUserTitle($sep = null)
	{
		if ($sep === null) $sep = ' > ';

		if (!$this->parent) {

			return $this->getUserTitle();
		}

		return $this->parent->getUserTitle() . $sep . $this->getUserTitle();
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
	 * @return array
	 */

	public function getChildrenOrdered()
	{
		$children = $this->children->toArray();

		uasort($children, function($a, $b) {
			if ($a->display_order == $b->display_order) {
				return 0;
			}

			return ($a->display_order < $b->display_order) ? -1 : 1;
		});

		return $children;
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
	 * @return \Doctrine\Common\Collections\ArrayCollection|null
	 */

	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * Return a unique ID that we can use to look up translations for this object
	 *
	 * @param string $property If supplied, the property on the object we want to translate.
	 * @param Translate $translate
	 * @return string
	 */

	public function getPhraseName($property = null, Translate $translate)
	{
		if (!$property) {

			$property = 'title';
		}

		$phrase_name = 'obj_department.' . $this->id . '_' . $property;

		return $phrase_name;
	}

	/**
	 * Get the default value phrase for the object
	 *
	 * @param string $property If supplied, the property on the object we want to translate.
	 * @param Translate $translate
	 * @return string
	 */

	public function getPhraseDefault($property = null, Translate $translate)
	{
		if ($property == 'full') {

			return $this->getRealTitle();
		}

		if ($property == 'user' && $this->user_title) {

			return $this->user_title;
		}

		return $this->title;
	}

	/**
	 * @return string
	 */

	public function __toString()
	{
		return $this->getFullTitle();
	}

	############################################################################
	# Validation Metadata
	############################################################################

	public function _validateParent(ExecutionContextInterface $context)
	{
		if (!$this->parent) return;

		if ($this->parent == $this) {
			$context->addViolationAt('parent', '[ParentNotSelf] Parent cannot be set to self');
		}
	}

	public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('title', new NotBlank());
		$metadata->addConstraint(new Callback(array(
			'methods' => array('_validateParent')
		)));
	}

	/**
	 * {@inheritDoc}
	 */
	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);
		$data['user_title'] = $this->getRealUserTitle();

		if ($this->parent) {
			$data['title_full']       = $this->parent->title . ' > ' . $this->title;
			$data['parent_id']        = $this->parent->getId();
			$data['parent_ids']       = array($this->parent->getId());
			$data['title_parts']      = array($this->parent->title, $this->title);
			$data['user_title_parts'] = array($this->parent->getUserTitle(), $this->getUserTitle());
			$data['has_children']     = false;
		} else {
			$data['title_full']       = $this->title;
			$data['parent_id']        = null;
			$data['parent_ids']       = array();
			$data['title_parts']      = array($this->title);
			$data['user_title_parts'] = array($this->getUserTitle());
			$data['has_children']     = count($this->children) != 0;
		}

		return $data;
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Department';
		$metadata->setPrimaryTable(array('name' => 'departments',));

		$metadata->mapField(
			array(
				 'fieldName'  => 'id',
				 'type'       => 'integer',
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'id',
				 'id'         => true,
			)
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'title',
				 'type'       => 'string',
				 'length'     => 255,
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'title',
			)
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'user_title',
				 'type'       => 'string',
				 'length'     => 255,
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'user_title',
			)
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'is_tickets_enabled',
				 'type'       => 'boolean',
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'is_tickets_enabled',
			)
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'is_chat_enabled',
				 'type'       => 'boolean',
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'is_chat_enabled',
			)
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'display_order',
				 'type'       => 'integer',
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'display_order',
			)
		);
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(
			array(
				 'fieldName'    => 'parent',
				 'targetEntity' => 'Application\\DeskPRO\\Entity\\Department',
				 'mappedBy'     => null,
				 'inversedBy'   => 'children',
				 'joinColumns'  => array(
					 0 => array(
						 'name'                 => 'parent_id',
						 'referencedColumnName' => 'id',
						 'nullable'             => true,
						 'onDelete'             => 'cascade',
						 'columnDefinition'     => null,
					 ),
				 ),
			)
		);
		$metadata->mapOneToMany(
			array(
				 'fieldName'    => 'children',
				 'targetEntity' => 'Application\\DeskPRO\\Entity\\Department',
				 'mappedBy'     => 'parent',
				 'orderBy'      => array('display_order' => 'ASC',),
				 'indexBy'      => 'id'
			)
		);
	}
}
