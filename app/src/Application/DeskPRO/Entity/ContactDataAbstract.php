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

use Application\DeskPRO\ContactData\ContactData;

/**
 * Contact data is stuff like address, instant messaging, phone etc.
 * These can be applied to People and Organizations.
 *
 * Because of the nature, each 'data_type' uses each of the field1-field10
 * differently. Sometimes only a single one might be used, other times multiple.
 *
 */
abstract class ContactDataAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 *
	 */
	protected $id = null;

	/**
	 * The handler class
	 *
	 * @var string
	 */
	protected $contact_type;

	/**
	 * The label/comment/name for this contact entry (Work, Home, etc).
	 *
	 * @var string
	 */
	protected $comment = '';

	/**
	 * @var string
	 */
	protected $field_1 = '';

	/**
	 * @var string
	 */
	protected $field_2 = '';

	/**
	 * @var string
	 */
	protected $field_3 = '';

	/**
	 * @var string
	 */
	protected $field_4 = '';

	/**
	 * @var string
	 */
	protected $field_5 = '';

	/**
	 * @var string
	 */
	protected $field_6 = '';

	/**
	 * @var string
	 */
	protected $field_7 = '';

	/**
	 * @var string
	 */
	protected $field_8 = '';

	/**
	 * @var string
	 */
	protected $field_9 = '';

	/**
	 * @var string
	 */
	protected $field_10 = '';

	/**
	 * Instance of the handler class
	 * @var \Application\DeskPRO\ContactData\AbstractContactData
	 */
	protected $_handler = null;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

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
