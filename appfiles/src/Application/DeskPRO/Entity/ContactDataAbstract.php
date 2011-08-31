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

use Application\DeskPRO\ContactData\ContactData;

/**
 * Contact data is stuff like address, instant messaging, phone etc.
 * These can be applied to People and Organizations.
 *
 * Because of the nature, each 'data_type' uses each of the field1-field10
 * differently. Sometimes only a single one might be used, other times multiple.
 *
 * @ORM_Mapping\MappedSuperclass
 */
abstract class ContactDataAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * The handler class
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="contact_type", type="string", length=80)
	 */
	protected $contact_type;

	/**
	 * The label/comment/name for this contact entry (Work, Home, etc).
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="comment", type="string", length=100)
	 */
	protected $comment = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="field_1", type="text")
	 */
	protected $field_1 = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="field_2", type="text")
	 */
	protected $field_2 = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="field_3", type="text")
	 */
	protected $field_3 = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="field_4", type="text")
	 */
	protected $field_4 = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="field_5", type="text")
	 */
	protected $field_5 = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="field_6", type="text")
	 */
	protected $field_6 = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="field_7", type="text")
	 */
	protected $field_7 = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="field_8", type="text")
	 */
	protected $field_8 = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="field_9", type="text")
	 */
	protected $field_9 = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="field_10", type="text")
	 */
	protected $field_10 = '';

	/**
	 * Instance of the handler class
	 * @var \Application\DeskPRO\ContactData\AbstractContactData
	 */
	protected $_handler = null;


	/**
	 * Get the DeskPRO form field object that knows how to render data etc.
	 *
	 * @return \Application\DeskPRO\ContactData\AbstractContactData
	 */
	public function getHandler()
	{
		if ($this->_handler !== null) return $this->_handler;
		$this->_handler = ContactData::getHandler($this->contact_type);

		return $this->_handler;
	}


	/**
	 * @param array $input
	 * @return void
	 */
	public function applyFormData(array $input)
	{
		$this->getHandler()->applyFormData($input, $this);
	}


	/**
	 * Get values that will be useful in a template.
	 *
	 * @return string
	 */
	public function getTemplateVars()
	{
		$vars = $this->getHandler()->getTemplateVars($this);
		$vars['contact_type'] = $this->getHandler()->getContactType();
		$vars['id'] = $this->id;
		$vars['rec'] = $this;

		return $vars;
	}
}
