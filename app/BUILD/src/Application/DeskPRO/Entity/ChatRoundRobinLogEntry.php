<?php

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
            'phrase' => 'adm.round_robins.log_no_agents_online_broadcast',
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

    public function addActionAssignedLeastUtilized(Person $person, \DateTime $lastActivity)
    {
        $this->actions[] = [
            'phrase' => 'adm.round_robins.log_assigned_last_activity',
            'params' => ['name' => $person->getDisplayName(), 'last_activity' => $lastActivity->format('Y-m-d H:i')],
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

    public function addActionSkippedNoPerm(Person $person)
    {
        $this->actions[] = [
            'phrase' => 'adm.round_robins.log_skipped_no_permissions',
            'params' => ['name' => $person->getDisplayName()],
        ];
    }

    public function addActionSkippedNoPermDep(Person $person)
    {
        $this->actions[] = [
            'phrase' => 'adm.round_robins.log_skipped_no_permissions_dep',
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
