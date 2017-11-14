<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

class ChatRoundRobinLogEntry extends DomainObject
{
    /** @var int */
    protected $id;
    /** @var ChatRoundRobin */
    protected $rr;
    /** @var int */
    protected $chatId;
    /** @var string */
    protected $chatSubject;
    /** @var array */
    protected $actions;
    /** @var \DateTime */
    protected $created;

    protected $translate;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->actions = [];
    }

    public function addActionNoOnline()
    {
        $this->actions[] = [
            'phrase' => 'adm.round_robins.log_no_agents_online',
            'params' => [],
        ];
    }

    public function addActionAssigned(Person $person)
    {
        $this->actions[] = [
            'phrase' => 'adm.round_robins.log_assigned',
            'params' => ['name' => $person->getDisplayName()],
        ];
    }

    public function addActionSkippedOffline(Person $person)
    {
        $this->actions[] = [
            'phrase' => 'adm.round_robins.log_skipped_offline',
            'params' => ['name' => $person->getDisplayName()],
        ];
    }

    public function addActionSkippedDisabled(Person $person)
    {
        $this->actions[] = [
            'phrase' => 'adm.round_robins.log_skipped_disabled',
            'params' => ['name' => $person->getDisplayName()],
        ];
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setPrimaryTable(['name' => 'chat_round_robin_log']);

        $metadata->mapField([
            'fieldName'  => 'id',
            'columnName' => 'id',
            'type'       => 'integer',
            'id'         => true,
        ]);

        $metadata->mapField([
            'fieldName'  => 'chatId',
            'columnName' => 'chat_id',
            'type'       => 'integer',
        ]);

        $metadata->mapField([
            'fieldName'  => 'chatSubject',
            'columnName' => 'chat_subject',
        ]);

        $metadata->mapField([
            'fieldName'  => 'actions',
            'columnName' => 'actions',
            'type'       => 'array',
        ]);

        $metadata->mapField([
            'fieldName'  => 'created',
            'columnName' => 'created',
            'type'       => 'datetime',
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'rr',
            'targetEntity' => ChatRoundRobin::class,
            'joinColumns'  => [
                [
                    'onDelete' => 'cascade',
                ],
            ],
        ]);
    }
}
