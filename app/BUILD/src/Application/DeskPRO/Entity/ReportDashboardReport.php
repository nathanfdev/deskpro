<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 * This is just a tab, that holds a collection of widgets
 * Yes it has columns, and widgets and report with which it appears in given dashboard.
 *
 * @property int    $id
 * @property string $title
 */
class ReportDashboardReport extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string it is a tab title
     */
    protected $title = '';

    /**
     * @var int
     */
    protected $columns;

    /**
     * @var ArrayCollection
     */
    protected $widgets;

    /**
     * @var int
     */
    protected $sort_order;

    /**
     * @var ReportDashboard
     */
    protected $dashboard;

    /**
     * @var array
     */
    protected $variables;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
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
     * @return int
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param int $columns
     *
     * @return $this
     */
    public function setColumns($columns)
    {
        $this->columns = (int) $columns;

        return $this;
    }

    /**
     * @return ReportDashboardWidget[]|ArrayCollection
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @param ReportDashboardWidget $widget
     *
     * @return $this
     */
    public function addWidget(ReportDashboardWidget $widget)
    {
        $this->widgets->add($widget);

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param int $sort_order
     *
     * @return $this
     */
    public function setSortOrder($sort_order)
    {
        $this->sort_order = $sort_order;

        return $this;
    }

    /**
     * @return ReportDashboard
     */
    public function getDashboard()
    {
        return $this->dashboard;
    }

    /**
     * @param ReportDashboard $dashboard
     *
     * @return $this
     */
    public function setDashboard($dashboard)
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     *
     * @return $this
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name' => 'report_dashboard_report',
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
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
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'sort_order',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'default'    => 0,
                'nullable'   => false,
                'columnName' => 'sort_order',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'columns',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'default'    => 24,
                'columnName' => 'columns',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'variables',
                'type'       => 'json_array',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'variables',
            ]
        );

        $metadata->mapOneToMany([
            'fieldName'    => 'widgets',
            'targetEntity' => ReportDashboardWidget::class,
            'mappedBy'     => 'report',
            'inversedBy'   => null,
            'orderBy'      => ['position' => 'ASC'],
            'cascade'      => ['persist', 'remove'],
        ]);

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'dashboard',
                'targetEntity' => ReportDashboard::class,
                'mappedBy'     => null,
                'inversedBy'   => 'reports',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'dashboard_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
