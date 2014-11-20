<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

class LogRequestStat extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var string
     */
    protected $request_id = '';

    /**
     * @var
     */
    protected $user_agent = '';

    /**
     * @var string
     */
    protected $user_ip = '0.0.0.0';

    /**
     * @var string
     */
    protected $page_id = '';

    /**
     * @var string
     */
    protected $page_url = '';

    /**
     * @var string
     */
    protected $response_type = '';

    /**
     * @var int
     */
    protected $response_code = 0;

    /**
     * @var int
     */
    protected $response_size = 0;

    /**
     * @var int
     */
    protected $query_count = 0;

    /**
     * @var float
     */
    protected $time_php = 0.00;

    /**
     * @var float
     */
    protected $time_db = 0.00;

    /**
     * @var float
     */
    protected $time_end = 0.00;

    /**
     * @var float
     */
    protected $time_userend = 0.00;

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->setPrimaryTable(array(
            'name'    => 'log_request_stats',
            'indexes' => array(
                'date_created_idx' => array('columns' => array('date_created', 'request_id')),
            ),
        ));

        $metadata->mapField(array(
            'id'         => true,
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'date_created',
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'request_id',
            'fieldName'  => 'request_id',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'user_agent',
            'fieldName'  => 'user_agent',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'user_ip',
            'fieldName'  => 'user_ip',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'page_id',
            'fieldName'  => 'page_id',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'page_url',
            'fieldName'  => 'page_url',
            'type'       => 'string',
            'length'     => 1000,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'response_type',
            'fieldName'  => 'response_type',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'response_code',
            'fieldName'  => 'response_code',
            'type'       => 'integer',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'response_size',
            'fieldName'  => 'response_size',
            'type'       => 'integer',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'query_count',
            'fieldName'  => 'query_count',
            'type'       => 'integer',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'time_php',
            'fieldName'  => 'time_php',
            'type'       => 'decimal',
            'precision'  => 8,
            'scale'      => 4,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'time_db',
            'fieldName'  => 'time_db',
            'type'       => 'decimal',
            'precision'  => 8,
            'scale'      => 4,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'time_end',
            'fieldName'  => 'time_end',
            'type'       => 'decimal',
            'precision'  => 8,
            'scale'      => 4,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'time_userend',
            'fieldName'  => 'time_userend',
            'type'       => 'decimal',
            'precision'  => 8,
            'scale'      => 4,
            'nullable'   => false,
        ));
    }
}
