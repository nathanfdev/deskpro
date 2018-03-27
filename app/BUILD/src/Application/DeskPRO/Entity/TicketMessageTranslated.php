<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class TicketMessageTranslated extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * @var TicketMessage
     */
    protected $ticket_message;

    /**
     * @var string
     */
    protected $from_lang_code;

    /**
     * @var string
     */
    protected $lang_code;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var \DateTime
     */
    protected $date_created = null;

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @param TicketMessage $ticketMessage
     */
    public function setTicketMessage(TicketMessage $ticketMessage)
    {
        $this->setModelField('ticket_message', $ticketMessage);
        $this->setModelField('ticket', $ticketMessage->ticket);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketMessageTranslated';
        $metadata->setPrimaryTable(['name' => 'tickets_messages_translated']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
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
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'from_lang_code',
            'type'       => 'string',
            'length'     => 80,
            'nullable'   => false,
            'columnName' => 'from_lang_code',
        ]);
        $metadata->mapField([
            'fieldName'  => 'lang_code',
            'type'       => 'string',
            'length'     => 80,
            'nullable'   => false,
            'columnName' => 'lang_code',
        ]);
        $metadata->mapField([
            'fieldName'  => 'message',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'message',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'ticket',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'ticket_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'ticket_message',
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
