<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\Basic;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Custom billing data.
 */
class CustomDataBilling extends CustomDataAbstract
{
    /**
     * @var TicketCharge
     */
    protected $ticket_charge;

    /**
     * @var CustomDefBilling
     */
    protected $field;

    /**
     * @var CustomDefBilling
     */
    protected $root_field;

    /**
     * @return int
     */
    public function getBillingId()
    {
        return $this->ticket_charge['id'];
    }

    /**
     * Set a field.
     *
     * @param CustomDefBilling $field
     *
     * @return $this
     */
    public function setField(CustomDefBilling $field = null)
    {
        $this->setModelField('field', $field);

        return $this;
    }

    /**
     * Set a root field.
     *
     * @param CustomDefBilling $field
     *
     * @return $this
     */
    public function setRootField(CustomDefBilling $field = null)
    {
        $this->setModelField('root_field', $field);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return TicketCharge
     */
    public function getOwner()
    {
        return $this->ticket_charge;
    }

    /**
     * @return TicketCharge
     */
    public function getTicketCharge()
    {
        return $this->ticket_charge;
    }

    /**
     * @param TicketCharge $ticket_charge
     *
     * @return $this
     */
    public function setTicketCharge(TicketCharge $ticket_charge = null)
    {
        $this->setModelField('ticket_charge', $ticket_charge);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = Basic::class;
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'              => 'custom_data_billing',
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
                'type'       => 'bigint',
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
                'fieldName'    => 'ticket_charge',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketCharge',
                'inversedBy'   => 'custom_data',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'ticket_charge_id',
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
                'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDefBilling',
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
                'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDefBilling',
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
    }
}
