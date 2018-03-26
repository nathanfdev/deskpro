<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class TicketMessageEmailId extends \Application\DeskPRO\Domain\DomainObject
{
    protected $id;

    protected $message;

    protected $email_id;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
//		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketMessageEmailId';
        $metadata->setPrimaryTable([
            'name'    => 'tickets_message_email_id',
            'indexes' => [
                'email_id_idx' => ['columns' => ['email_id']],
            ],
        ]);

        $metadata->mapField([
            'fieldName' => 'id',
            'id'        => true,
            'type'      => 'integer',
        ]);

        $metadata->mapField([
            'fieldName'  => 'email_id',
            'nullable'   => false,
            'columnName' => 'email_id',
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'message',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMessage',
            'inversedBy'   => 'email_message_id',
            'joinColumns'  => [
                [
                    'name'                 => 'message_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'CASCADE',
                ],
            ],
        ]);
    }
}
