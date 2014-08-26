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

/**
 * A log of API requests per key
 *
 */
class ApiKeyLog extends \Application\DeskPRO\Domain\DomainObject
{
    /**
	 * @var int
	 */
	protected $id = null;
	
    /**
	 * @var int
	 * @var \Application\DeskPRO\Entity\ApiKey
	 */
	protected $key;

	/**
	 * @var int
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $request;
        
	/**
	 * @var int
	 */
	protected $response;

	/**
	 * @var int
	 */
	protected $time;

	public function __construct()
	{
		$this['time'] = time();
		$this['request'] = array();
		$this['response'] = array();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
        return $this->id;
	}

	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);
		$data['time'] = date('Y-m-d H:i:s', $data['time']);

		return $data;
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ApiKeyLog';

		$metadata->setPrimaryTable(array(
			'name' => 'api_key_log',
		));

		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

		$metadata->mapField(array(
	        'fieldName' => 'id',
	        'type' => 'integer',
	        'nullable' => false,
	        'columnName' => 'id',
	        'id' => true,
        ));

		$metadata->mapField(array(
			'fieldName' => 'time',
			'type' => 'integer',
			'nullable' => false,
			'columnName' => 'time',
		));

		$metadata->mapField(array(
			'fieldName' => 'request',
			'type' => 'array',
			'nullable' => false,
			'columnName' => 'request',
		));

		$metadata->mapField(array(
			'fieldName' => 'response',
			'type' => 'array',
			'nullable' => false,
			'columnName' => 'response',
		));

		$metadata->mapManyToOne(array(
			'fieldName' => 'key',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\ApiKey',
			'mappedBy' => null,
			'inversedBy' => 'logs',
			'joinColumns' => array(
				0 => array(
					'name' => 'key_id',
					'referencedColumnName' => 'id',
					'nullable' => false,
					'onDelete' => 'cascade',
				),
			),
		));

	    $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}
