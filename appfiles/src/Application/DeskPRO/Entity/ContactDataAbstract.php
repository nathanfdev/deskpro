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

/**
 * Contact data is stuff like address, instant messaging, phone etc.
 * These can be applied to People and Organizations.
 *
 * Because of the nature, each 'data_type' uses each of the field1-field10
 * differently. Sometimes only a single one might be used, other times multiple.
 *
 * @orm:MappedSuperclass
 */
abstract class ContactDataAbstract extends \Application\DeskPRO\Domain\DomainObject
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
	 * The handler class
	 *
	 * @var string
	 * @orm:Column(name="handler_class", type="string", length=80)
	 */
	protected $handler_class;

	/**
	 * The label/comment/name for this contact entry (Work, Home, etc).
	 *
	 * @var string
	 * @orm:Column(name="comment", type="string", length=100)
	 */
	protected $comment = '';

	/**
	 * @var string
	 * @orm:Column(name="field_1", type="text")
	 */
	protected $field_1 = '';

	/**
	 * @var string
	 * @orm:Column(name="field_2", type="text")
	 */
	protected $field_2 = '';

	/**
	 * @var string
	 * @orm:Column(name="field_3", type="text")
	 */
	protected $field_3 = '';

	/**
	 * @var string
	 * @orm:Column(name="field_4", type="text")
	 */
	protected $field_4 = '';

	/**
	 * @var string
	 * @orm:Column(name="field_5", type="text")
	 */
	protected $field_5 = '';

	/**
	 * @var string
	 * @orm:Column(name="field_6", type="text")
	 */
	protected $field_6 = '';

	/**
	 * @var string
	 * @orm:Column(name="field_7", type="text")
	 */
	protected $field_7 = '';

	/**
	 * @var string
	 * @orm:Column(name="field_8", type="text")
	 */
	protected $field_8 = '';

	/**
	 * @var string
	 * @orm:Column(name="field_9", type="text")
	 */
	protected $field_9 = '';

	/**
	 * @var string
	 * @orm:Column(name="field_10", type="text")
	 */
	protected $field_10 = '';

	/**
	 * Instance of the handler class
	 * @var TODO
	 */
	protected $_handler_instance = null;



	/**
	 * Get the DeskPRO form field object that knows how to render data etc.
	 *
	 * @return Application\DeskPRO\Form\FieldHandler\AbstractFieldHandler
	 */
	public function getHandler()
	{
		if ($this->_handler_instance !== null) return $this->_handler_instance;

		$classname = $this->handler_class;

		$this->_handler_instance = new $classname($this);

		return $this->_handler_instance;
	}
}