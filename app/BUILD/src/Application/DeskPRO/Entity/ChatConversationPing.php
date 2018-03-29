<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A simple table that records pings from users on speciifc chats. This is to avoid running updates
 * against the chat table every poll.
 *
 * These are cleaned up often.
 */
class ChatConversationPing extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var int
     */
    protected $chat_id = null;

    /**
     * @var int
     */
    protected $ping_time = 0;

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ChatConversationPing';
        $metadata->setPrimaryTable([
            'name'    => 'chat_conversation_pings',
            'indexes' => [
                'chat_id_idx' => ['columns' => ['chat_id']],
            ],
        ]);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'chat_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'chat_id',
        ]);
        $metadata->mapField([
            'fieldName'  => 'ping_time',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'ping_time',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
