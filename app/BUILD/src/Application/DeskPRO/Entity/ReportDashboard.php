<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class ReportDashboard.
 */
class ReportDashboard extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var ArrayCollection|ReportDashboardReport[]
     */
    protected $reports;

    /**
     * @var bool
     *           In case that default reports are comes only with upgrade script we have only getter here
     *
     * @see ReportDashboard::isDefault()
     */
    protected $is_default = false;

    /**
     * @var int
     */
    protected $display_order = 0;

    /**
     * @var bool
     */
    protected $isAgent = false;

    /**
     * @var ArrayCollection|ReportDashboardPermission[]
     */
    protected $permissions;

    /**
     * @var string
     */
    protected $system_name;

    /**
     * @var Person - an owner for this dashboard
     */
    protected $person;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->reports     = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return ReportDashboardReport[]|ArrayCollection
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @param ReportDashboardReport $report
     *
     * @return $this
     */
    public function addReport(ReportDashboardReport $report)
    {
        $this->reports->add($report);
        $report->setDashboard($this);

        return $this;
    }

    /**
     * @param ReportDashboardReport $report
     *
     * @return $this
     */
    public function removeReport(ReportDashboardReport $report)
    {
        $this->reports->removeElement($report);
        $report->setDashboard(null);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return (bool) $this->is_default;
    }

    /**
     * @param bool $default
     *
     * @return $this
     */
    public function setIsDefault($default)
    {
        $this->is_default = (bool) $default;

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param int $display_order
     */
    public function setDisplayOrder($display_order)
    {
        $this->display_order = $display_order;
    }

    /**
     * @return bool
     */
    public function isAgent()
    {
        return $this->isAgent;
    }

    /**
     * @param bool $isAgent
     *
     * @return $this
     */
    public function setIsAgent($isAgent)
    {
        $this->setModelField('isAgent', $isAgent);

        return $this;
    }

    /**
     * @return ReportDashboardPermission[]|ArrayCollection
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @return string
     */
    public function getSystemName()
    {
        return $this->system_name;
    }

    /**
     * @param string $system_name
     *
     * @return $this
     */
    public function setSystemName($system_name)
    {
        $this->system_name = $system_name;

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable([
            'name' => 'report_dashboard',
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
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
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'title',
        ]);
        $metadata->mapField([
            'fieldName'  => 'system_name',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'system_name',
        ]);

        $metadata->mapField([
            'fieldName'  => 'is_default',
            'default'    => false,
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_default',
        ]);

        $metadata->mapField(
            [
                'fieldName'  => 'display_order',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'default'    => 0,
                'nullable'   => false,
                'columnName' => 'display_order',
            ]
        );
        $metadata->mapField([
            'fieldName'  => 'isAgent',
            'default'    => false,
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_agent',
        ]);

        $metadata->mapOneToMany([
            'fieldName'     => 'reports',
            'targetEntity'  => ReportDashboardReport::class,
            'mappedBy'      => 'dashboard',
            'inversedBy'    => null,
            'orderBy'       => ['sort_order' => 'ASC', 'id' => 'ASC'],
            'cascade'       => ['persist', 'remove'],
            'orphanRemoval' => true,
        ]);
        $metadata->mapOneToMany([
            'fieldName'     => 'permissions',
            'targetEntity'  => ReportDashboardPermission::class,
            'mappedBy'      => 'dashboard',
            'inversedBy'    => null,
            'cascade'       => ['persist', 'remove'],
            'orphanRemoval' => true,
        ]);

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => Person::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'nullable'     => true,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
