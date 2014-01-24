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

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;


/**
 * @property int       $id
 * @property string    $account_Type
 * @property \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface $incoming_account
 * @property \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface $outgoing_account
 * @property bool      $is_enabled
 * @property string    $address
 * @property array     $other_addresses
 * @property array     $options
 * @property \DateTime $date_created
 * @property \DateTime $date_read_start
 * @property \DateTime $date_last_incoming
 */
class EmailAccount extends DomainObject
{
	/**
	 * This is an outgoing account only (no incoming reading ability)
	 */
	const TYPE_OUT      = 'outgoing';

	/**
	 * This is a ticket gateway account
	 */
	const TYPE_TICKETS  = 'tickets';

	/**
	 * This is an article gateway account
	 */
	const TYPE_ARTICLES = 'artices';


	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var string
	 */
	protected $account_type;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
	 */
	protected $incoming_account;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
	 */
	protected $outgoing_account;

	/**
	 * @var bool
	 */
	protected $is_enabled = true;

	/**
	 * @var string
	 */
	protected $address;

	/**
	 * @var array
	 */
	protected $other_addresses;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var \DateTime
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 */
	protected $date_read_start;

	/**
	 * @var \DateTime
	 */
	protected $date_last_incoming;


	/**
	 * @param string $account_type
	 */
	public function __construct($account_type)
	{
		$this->setAccountType($account_type);
		$this->date_created = new \DateTime();
	}


	/**
	 * @param string $account_type
	 * @throws \InvalidArgumentException
	 */
	public function setAccountType($account_type)
	{
		if (!in_array($account_type, array(
			self::TYPE_OUT,
			self::TYPE_TICKETS,
			self::TYPE_ARTICLES
		))) {
			throw new \InvalidArgumentException();
		}

		$this->setModelField('account_type', $account_type);
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType      = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->idGenerator          = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
		$metadata->setPrimaryTable(array('name' => 'email_accounts'));

		$metadata->mapField(array(
			'columnName' => 'id',
			'fieldName'  => 'id',
			'type'       => 'integer',
			'id'         => true,
			'nullable'   => false
		));
		$metadata->mapField(array(
			'columnName' => 'account_type',
			'fieldName'  => 'account_type',
			'type'       => 'string',
			'length'     => 255,
			'nullable'   => false
		));
		$metadata->mapField(array(
			'columnName' => 'incoming_account',
			'fieldName'  => 'incoming_account',
			'type'       => 'dp_json_obj',
			'nullable'   => true
		));
		$metadata->mapField(array(
			'columnName' => 'outgoing_account',
			'fieldName'  => 'outgoing_account',
			'type'       => 'dp_json_obj',
			'nullable'   => true
		));
		$metadata->mapField(array(
			'columnName' => 'is_enabled',
			'fieldName'  => 'is_enabled',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'address',
			'fieldName'  => 'address',
			'type'       => 'string',
			'length'     => 255,
			'nullable'   => false
		));
		$metadata->mapField(array(
			'columnName' => 'other_addresses',
			'fieldName'  => 'other_addresses',
			'type'       => 'simple_array',
			'nullable'   => false
		));
		$metadata->mapField(array(
			'columnName' => 'options',
			'fieldName'  => 'options',
			'type'       => 'json_array',
			'nullable'   => true
		));
		$metadata->mapField(array(
			'columnName' => 'date_created',
			'fieldName'  => 'date_created',
			'type'       => 'datetime',
			'nullable'   => false
		));
		$metadata->mapField(array(
			'columnName' => 'date_read_start',
			'fieldName'  => 'date_read_start',
			'type'       => 'datetime',
			'nullable'   => true
		));
		$metadata->mapField(array(
			'columnName' => 'date_last_incoming',
			'fieldName'  => 'date_last_incoming',
			'type'       => 'datetime',
			'nullable'   => true
		));
	}
}
