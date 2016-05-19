<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */
namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Flagged tickets.
 */
class TicketFlagged extends DomainObject
{
    const STAR_BLUE   = 1;
    const STAR_GREEN  = 2;
    const STAR_ORANGE = 3;
    const STAR_PINK   = 4;
    const STAR_PURPLE = 5;
    const STAR_RED    = 6;
    const STAR_YELLOW = 7;

    public static $colorMap = [
        self::STAR_BLUE   => 'blue',
        self::STAR_GREEN  => 'green',
        self::STAR_ORANGE => 'orange',
        self::STAR_PINK   => 'pink',
        self::STAR_PURPLE => 'purple',
        self::STAR_RED    => 'red',
        self::STAR_YELLOW => 'yellow',
    ];

    public static $hexMap = [
        self::STAR_BLUE   => '#0000FF',
        self::STAR_GREEN  => '#008000',
        self::STAR_ORANGE => '#FFA500',
        self::STAR_PINK   => '#FFC0CB',
        self::STAR_PURPLE => '#800080',
        self::STAR_RED    => '#FF0000',
        self::STAR_YELLOW => '#FFFF00',
    ];

    /**
     * @var int
     */
    protected $ticket_id = null;

    /**
     * @var int
     */
    protected $person_id = null;

    /**
     * @var string
     */
    protected $color = 'blue';

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketFlagged';
        $metadata->setPrimaryTable(
            [
                'name'    => 'tickets_flagged',
                'indexes' => [
                    // already have a PK index on (person_id, ticket_id),
                    // but need one on just ticket_id as well (used when merging or deleting tickets):
                    'ticket_id_idx' => ['columns' => ['ticket_id']],
                ],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'person_id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'person_id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'ticket_id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'ticket_id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'color',
                'type'       => 'string',
                'length'     => 20,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'color',
            ]
        );
    }
}
