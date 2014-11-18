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
use Application\DeskPRO\Entity;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class Ticket
 *
 * @property string $obj_type
 * @property string $ref
 */
class RefReserve extends DomainObject
{
    /**
     * @var string
     */
    protected $obj_type = null;

    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @var \DateTime
     */
    protected $date_created;

    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType = ClassMetadataInfo::GENERATOR_TYPE_NONE;
        $metadata->setPrimaryTable(array(
            'name' => 'ref_reserve'
        ));

        $metadata->mapField(array(
            'columnName' => 'obj_type',
            'fieldName'  => 'obj_type',
            'type'       => 'string',
            'length'     => 50,
            'nullable'   => false,
            'id'         => true
        ));
        $metadata->mapField(array(
            'columnName' => 'ref',
            'fieldName'  => 'ref',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
            'id'         => true
        ));
        $metadata->mapField(array(
            'fieldName'  => 'date_created',
            'columnName' => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
        ));
    }
}
