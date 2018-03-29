<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\TicketLayout\Layout;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * @property int                                      $id
 * @property Department                               $department
 * @property bool                                     $is_enabled
 * @property \Application\DeskPRO\TicketLayout\Layout $user_layout
 * @property \Application\DeskPRO\TicketLayout\Layout $agent_layout
 * @property \DateTime                                $date_updated
 * @JMS\ExclusionPolicy("all")
 */
class TicketLayout extends DomainObject
{
    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Department uses this layout.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var Department
     */
    protected $department;

    /**
     * Is layout enabled?
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_enabled = true;

    /**
     * Layout for users.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\TicketLayout\Layout>")
     *
     * @var \Application\DeskPRO\TicketLayout\Layout
     */
    protected $user_layout;

    /**
     * Layout for agents.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\TicketLayout\Layout>")
     *
     * @var \Application\DeskPRO\TicketLayout\Layout
     */
    protected $agent_layout;

    /**
     * Date when this stuff was created.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_updated;

    public function __construct(Department $department = null)
    {
        $this->department   = $department;
        $this->user_layout  = new Layout();
        $this->agent_layout = new Layout();
        $this->date_updated = new \DateTime();
    }

    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $dep
     */
    public function setDepartment(Department $dep = null)
    {
        if ($this->id) {
            throw new \RuntimeException('You cannot change the department once it has been set.');
        } else {
            $this->setModelField('department', $dep);
        }
    }

    /**
     * @return Layout
     */
    public function getUserLayout()
    {
        return $this->user_layout;
    }

    /**
     * @param Layout $userLayout
     */
    public function setUserLayout($userLayout)
    {
        $this->setModelField('user_layout', $userLayout);
    }

    /**
     * @return Layout
     */
    public function getAgentLayout()
    {
        return $this->agent_layout;
    }

    /**
     * @param Layout $agentLayout
     */
    public function setAgentLayout($agentLayout)
    {
        $this->setModelField('agent_layout', $agentLayout);
    }

    /**
     * @param \DateTime $dateUpdated
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->setModelField('date_updated', $dateUpdated);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $is_enabled
     *
     * @return $this
     */
    public function setIsEnabled($is_enabled)
    {
        $this->setModelField('is_enabled', $is_enabled);

        return $this;
    }

    /**
     * Enable the layout.
     */
    public function enable()
    {
        $this['is_enabled'] = true;
    }

    /**
     * Disable the layout.
     */
    public function disable()
    {
        $this['is_enabled'] = true;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType      = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType        = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

        $metadata->setPrimaryTable([
            'name' => 'ticket_layouts',
        ]);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
            'id'         => true,
        ]);
        $metadata->mapField([
            'columnName' => 'is_enabled',
            'fieldName'  => 'is_enabled',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'user_layout',
            'fieldName'  => 'user_layout',
            'type'       => 'dp_json_obj',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'agent_layout',
            'fieldName'  => 'agent_layout',
            'type'       => 'dp_json_obj',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'date_updated',
            'fieldName'  => 'date_updated',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'department',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Department',
            'joinColumns'  => [[
                                   'name'                 => 'department_id',
                                   'referencedColumnName' => 'id',
                                   'nullable'             => true,
                                   'onDelete'             => 'cascade',
                               ]],
        ]);
    }
}
