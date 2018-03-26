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
 * A raw ticket message without any charset conversion into UTF-8.
 */
class TicketMessageRaw extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var \Application\DeskPRO\Entity\TicketMessage
     */
    protected $message = null;

    /**
     * The raw content.
     *
     * @var string
     */
    protected $raw = '';

    /**
     * The charset provided.
     *
     * @var string
     */
    protected $charset = 'UNKNOWN';

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setIdentifier(['message']);
        $metadata->setPrimaryTable(['name' => 'tickets_messages_raw']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'raw',
            'type'       => 'dpblob_file',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'raw',
        ]);
        $metadata->mapField([
            'fieldName'  => 'charset',
            'type'       => 'string',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'charset',
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'message',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMessage',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'message_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
