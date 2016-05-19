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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Custom billing data.
 */
class CustomDataBilling extends CustomDataAbstract
{
    /**
     * @var \Application\DeskPRO\Entity\TicketCharge
     */
    protected $ticket_charge;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefBilling
     */
    protected $field = null;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefBilling
     */
    protected $root_field = null;

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

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'    => 'custom_data_billing',
                'indexes' => [
                    'field_id_idx' => ['columns' => ['field_id', 'ticket_charge_id']],
                ],
                'uniqueConstraints' => [
                    'unique_idx' => ['columns' => ['field_id', 'ticket_charge_id', 'root_field_id']],
                ],
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
                'fieldName'    => 'ticket_charge',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketCharge',
                'inversedBy'   => 'custom_data',
                'joinColumns'  => array(
                    0 => array(
                        'name'                 => 'ticket_charge_id',
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
                'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDefBilling',
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
                'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDefBilling',
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
    }
}
