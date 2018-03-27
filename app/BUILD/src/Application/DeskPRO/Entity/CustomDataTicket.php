<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\CustomDataTicket as CustomDataTicketRepository;
use DeskPRO\Bundle\AppBundle\EventListener\Doctrine\CustomDataChangeListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Custom ticket data.
 */
class CustomDataTicket extends CustomDataAbstract
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $ticket;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefTicket
     */
    protected $field = null;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefTicket
     */
    protected $root_field = null;

    /**
     * Set a field.
     *
     * @param CustomDefTicket $field
     *
     * @return $this
     */
    public function setField(CustomDefTicket $field = null)
    {
        $this->setModelField('field', $field);

        return $this;
    }

    /**
     * @return CustomDefAbstract
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setTicket(Ticket $ticket)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    /**
     * Set a root field.
     *
     * @param CustomDefTicket $field
     *
     * @return $this
     */
    public function setRootField(CustomDefTicket $field = null)
    {
        $this->setModelField('root_field', $field);

        return $this;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return int
     */
    public function getTicketId()
    {
        return $this->ticket['id'];
    }

    /**
     * @return CustomDefTicket
     */
    public function getRootField()
    {
        return $this->root_field;
    }

    /**
     * {@inheritdoc}
     *
     * @return Ticket
     */
    public function getOwner()
    {
        return $this->ticket;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = CustomDataTicketRepository::class;
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'              => 'custom_data_ticket',
                'uniqueConstraints' => [],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'value',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'value',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'input',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'input',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'ticket',
                'targetEntity' => Ticket::class,
                'inversedBy'   => 'custom_data',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'ticket_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'field',
                'targetEntity' => CustomDefTicket::class,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'root_field',
                'targetEntity' => CustomDefTicket::class,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'root_field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );

        $metadata->addEntityListener(Events::postPersist, CustomDataChangeListener::class, Events::postPersist);
        $metadata->addEntityListener(Events::preUpdate, CustomDataChangeListener::class, Events::preUpdate);
        $metadata->addEntityListener(Events::preRemove, CustomDataChangeListener::class, Events::preRemove);
    }
}
