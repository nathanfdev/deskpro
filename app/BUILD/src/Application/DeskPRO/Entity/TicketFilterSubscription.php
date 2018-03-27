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
 * A simple record that just holds filter subscriptions for agents.
 */
class TicketFilterSubscription extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\LegacyTicketFilter
     */
    protected $filter;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var bool
     */
    protected $email_created = false;

    /**
     * @var bool
     */
    protected $email_new = false;

    /**
     * @var bool
     */
    protected $email_leave = false;

    /**
     * @var bool
     */
    protected $email_user_activity = false;

    /**
     * @var bool
     */
    protected $email_agent_activity = false;

    /**
     * @var bool
     */
    protected $email_agent_note = false;

    /**
     * @var bool
     */
    protected $email_property_change = false;

    /**
     * @var bool
     */
    protected $alert_created = false;

    /**
     * @var bool
     */
    protected $alert_new = false;

    /**
     * @var bool
     */
    protected $alert_leave = false;

    /**
     * @var bool
     */
    protected $alert_user_activity = false;

    /**
     * @var bool
     */
    protected $alert_agent_activity = false;

    /**
     * @var bool
     */
    protected $alert_agent_note = false;

    /**
     * @var bool
     */
    protected $alert_property_change = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketFilterSubscription';
        $metadata->setPrimaryTable(['name' => 'ticket_filter_subscriptions']);
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
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapField([
            'fieldName'  => 'email_created',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'email_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'email_new',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'email_new',
        ]);
        $metadata->mapField([
            'fieldName'  => 'email_leave',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'email_leave',
        ]);
        $metadata->mapField([
            'fieldName'  => 'email_user_activity',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'email_user_activity',
        ]);
        $metadata->mapField([
            'fieldName'  => 'email_agent_activity',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'email_agent_activity',
        ]);
        $metadata->mapField([
            'fieldName'  => 'email_agent_note',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'email_agent_note',
        ]);
        $metadata->mapField([
            'fieldName'  => 'email_property_change',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'email_property_change',
        ]);
        $metadata->mapField([
            'fieldName'  => 'alert_created',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'alert_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'alert_new',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'alert_new',
        ]);
        $metadata->mapField([
            'fieldName'  => 'alert_leave',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'alert_leave',
        ]);
        $metadata->mapField([
            'fieldName'  => 'alert_user_activity',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'alert_user_activity',
        ]);
        $metadata->mapField([
            'fieldName'  => 'alert_agent_activity',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'alert_agent_activity',
        ]);
        $metadata->mapField([
            'fieldName'  => 'alert_agent_note',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'alert_agent_note',
        ]);
        $metadata->mapField([
            'fieldName'  => 'alert_property_change',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'alert_property_change',
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'filter',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\LegacyTicketFilter',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'filter_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
