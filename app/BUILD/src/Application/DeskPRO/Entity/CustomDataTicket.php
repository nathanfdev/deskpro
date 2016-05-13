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

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\CustomDataTicket';
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'    => 'custom_data_ticket',
                'indexes' => [
                    'field_id_idx' => ['columns' => [0 => 'field_id', 1 => 'ticket_id']],
                ],
                // TODO: This can't be here until this is fixed https://trello.com/c/mHvpJsZF
                //'uniqueConstraints' => [
                //    'unique_idx' => ['columns' => ['field_id', 'ticket_id', 'root_field_id']],
                //],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            array(
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'value',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'value',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'input',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'input',
            )
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            array(
                'fieldName'    => 'ticket',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
                'inversedBy'   => 'custom_data',
                'joinColumns'  => array(
                    0 => array(
                        'name'                 => 'ticket_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ),
                ),
            )
        );
        $metadata->mapManyToOne(
            array(
                'fieldName'    => 'field',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDefTicket',
                'joinColumns'  => array(
                    0 => array(
                        'name'                 => 'field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ),
                ),
            )
        );
        $metadata->mapManyToOne(
            array(
                'fieldName'    => 'root_field',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDefTicket',
                'joinColumns'  => array(
                    0 => array(
                        'name'                 => 'root_field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ),
                ),
            )
        );

        $metadata->addEntityListener(Events::postPersist, CustomDataChangeListener::class, Events::postPersist);
        $metadata->addEntityListener(Events::preUpdate, CustomDataChangeListener::class, Events::preUpdate);
        $metadata->addEntityListener(Events::preRemove, CustomDataChangeListener::class, Events::preRemove);
    }
}
