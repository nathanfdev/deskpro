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
 * @orm:MappedSuperclass
 */
class FormField extends \DeskPRO\Domain\DomainObject
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
	 * @var FormField
	 * @orm:OneToOne(targetEntity="FormField")
	 * @orm:JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	//protected $parent = null;

	/**
	 * The title. Note this should be a phrase key, not an actual string.
	 * 
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title = '';

	/**
	 * The handler class
	 *
	 * @var string
	 * @orm:Column(name="handler_class", type="string", length=255)
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
	 * @var DeskPRO\Form\FieldHandler\AbstractFieldHandler
	 */
	protected $_handler_instance = null;


	public function __construct()
	{
		$this->field_children = new \Doctrine\Common\Collections\ArrayCollection();
	}
	

	/**
	 * Get the DeskPRO form field object that knows how to render data etc.
	 *
	 * @return DeskPRO\Form\FieldHandler\AbstractFieldHandler
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
}