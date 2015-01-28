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

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;


class RateLimitLog extends DomainObject
{
    /**
     * @var int
     */
    protected $id;

    protected $action;

    protected $ip;

    protected $person_id;

    protected $date_created;

    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    public function setIp($ip)
    {
        $this->setModelField('ip', ip2long($ip));
    }

    public function getIp()
    {
        return long2ip($this->ip);
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
            'name' => 'rate_limit_log',
            'indexes' => array(
                'search_idx' => array(
                    'columns' => array('action', 'date_created', 'ip'),
                ),
            ),
        ));


        $metadata->mapField(array(
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'action',
            'fieldName'  => 'action',
            'type'       => 'string',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'ip',
            'fieldName'  => 'ip',
            'type'       => 'integer',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'person_id',
            'fieldName'  => 'person_id',
            'type'       => 'integer',
            'nullable'   => true,
        ));
        $metadata->mapField(array(
            'columnName' => 'date_created',
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
        ));
    }
}
