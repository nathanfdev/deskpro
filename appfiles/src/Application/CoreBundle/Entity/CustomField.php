<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A form field is any custom field that can be attached to anything in the system.
 *
 * @MappedSuperclass
 */
class FormField extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The parent ID for multi-field fields.
	 *
	 * @var int
	 * @Column(name="parent_id", type="integer", nullable=true)
	 */
	protected $parent_id = null;

	/**
	 * The title. Note this should be a phrase key, not an actual string.
	 * 
	 * @var string
	 * @Column(name="title", type="string", length=255)
	 */
	protected $title = '';

	/**
	 * The handler class
	 *
	 * @var string
	 * @Column(name="handler_class", type="string", length=255)
	 */
	protected $handler_class;

	/**
	 * Options for the field
	 * 
	 * @Column(name="options", type="array")
	 */
	protected $options = array();

	/**
	 * Field children
	 * 
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @OneToMany(targetEntity="FormField", mappedBy="parent_id")
	 */
	protected $field_children = null;

	/**
	 * @var DeskPRO\Form\FieldHandler\AbstractFieldHandler
	 */
	protected $_handler_instance = null;

	

	/**
	 * Get the DeskPRO form field object that knows how to render data etc.
	 *
	 * @return DeskPRO\Form\FieldHandler\AbstractFieldHandler
	 */
	public function getHandler()
	{
		if ($this->_handler_instance !== null) return $this->_handler_instance;

		$classname = $this['handler_class'];

		$this->_handler_instance = new $classname($this);

		return $this->_handler_instance;
	}
}