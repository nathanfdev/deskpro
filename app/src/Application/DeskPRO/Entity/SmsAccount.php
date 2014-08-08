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
 * @subpackage
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use \Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\PhoneNumbers;

/**
 * @property int $id
 * @property string $type
 * @property array $params
 * @property string $identifier
 * @property bool $is_enabled
 * @property PhoneNumber|null $phone_number
 */
class SmsAccount extends DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var string the string type of the provider (SmsProviderInterface::getName)
	 */
	protected $type;

	/**
	 * @var array any parameters that the provider factory needs to create the provider of $type
	 */
	protected $params;

	/**
	 * @var string an identifier that we put next to the account in the UI
	 */
	protected $identifier;

	/**
	 * @var PhoneNumber a stored phone number that is used
	 */
	protected $phone_number;

	/**
	 * @var bool if the acount is enabled or not
	 */
	protected $is_enabled;


	public function __construct()
	{
		$this->is_enabled = false;
	}


	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);

		$data['phone_number'] = null;
		if ($this->phone_number) {
			$data['phone_number'] = PhoneNumbers::toInternationalFormat($this->phone_number->number);
		}
		$data['phone_number_region'] = $this->phone_number ? $this->phone_number->region : null;

		return $data;
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\SmsAccount';
		$metadata->setPrimaryTable(array('name' => 'sms_accounts'));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapField(
			array(
				'fieldName' => 'id', 'type' => 'integer', 'nullable' => false, 'columnName' => 'id', 'id' => true,
				'precision' => 0, 'scale' => 0,
			)
		);
		$metadata->mapField(
			array(
				'fieldName' => 'type', 'type' => 'string', 'length' => 20, 'precision' => 0, 'scale' => 0,
				'nullable'  => false, 'columnName' => 'type'
			)
		);
		$metadata->mapField(
			array(
				'fieldName' => 'params', 'type' => 'array', 'columnName' => 'params', 'nullable' => true
			)
		);
		$metadata->mapField(
			array(
				'fieldName' => 'identifier', 'type' => 'string', 'length' => 20, 'precision' => 0, 'scale' => 0,
				'nullable'  => true, 'columnName' => 'identifier',
			)
		);
		$metadata->mapField(
			array(
				'fieldName' => 'is_enabled', 'type' => 'boolean', 'columnName' => 'is_enabled',
			)
		);
		$metadata->mapOneToOne(
			array(
				'fieldName'    => 'phone_number',
				'targetEntity' => 'Application\\DeskPRO\\Entity\\PhoneNumber',
				'cascade'      => array('all')
			)
		);
	}
}
