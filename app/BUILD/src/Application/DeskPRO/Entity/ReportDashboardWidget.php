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
use Application\DeskPRO\Entity;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * ReportDashboardWidget.
 */
class ReportDashboardWidget extends DomainObject
{
    const WIDGET_TYPE_GRAPH     = 'graph';
    const WIDGET_TYPE_STAT      = 'stat';
    const WIDGET_TYPE_HARDCODED = 'hardcoded';
    const WIDGET_TYPE_BAR       = 'bar';
    const WIDGET_TYPE_PIE       = 'pie';
    const WIDGET_TYPE_TABLE     = 'table';

    /**
     * @var array
     */
    protected $widgetTypesMapping = [
        'simple_bars'  => self::WIDGET_TYPE_GRAPH,
        'bars'         => self::WIDGET_TYPE_GRAPH,
        'simple_lines' => self::WIDGET_TYPE_GRAPH,
        'lines'        => self::WIDGET_TYPE_GRAPH,
        'area'         => self::WIDGET_TYPE_GRAPH,
        'simple_area'  => self::WIDGET_TYPE_GRAPH,
        'pie'          => self::WIDGET_TYPE_GRAPH,
        'table'        => self::WIDGET_TYPE_TABLE,
        'simple_stat'  => self::WIDGET_TYPE_STAT,
    ];

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var ReportDashboardReport
     */
    protected $report = null;

    /**
     * @var ReportWidget it's a reference to ReportWidget Entity that holds DPQL
     */
    protected $widget = null;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $position = '0:0';

    /**
     * @var string
     */
    protected $size = '8:5';

    /**
     * @var string
     */
    protected $hc_data = null;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $variables;

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
        $this->id = (int) $id;

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
     * @return array
     */
    public function getPosition()
    {
        return explode(':', $this->position);
    }

    /**
     * @param string|array $position
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setPosition($position)
    {
        if (is_string($position) && false != /*that's right!*/
            strpos($position, ':')
        ) {
            $position = explode(':', $position);
        }

        if (is_array($position) && count($position) > 1) {
            $x = array_shift($position);
            $y = array_shift($position);
        } else {
            throw new \Exception('Wrong point given!');
        }
        $this->position = implode(':', [(int) $x, (int) $y]);

        return $this;
    }

    /**
     * @return ReportDashboardReport
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param ReportDashboardReport $report
     *
     * @return $this
     */
    public function setReport(ReportDashboardReport $report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return array
     */
    public function getSize()
    {
        return explode(':', $this->size);
    }

    /**
     * @param string $size
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setSize($size)
    {
        if (is_string($size) && false != /*that's right!*/
            strpos($size, ':')
        ) {
            $size = explode(':', $size);
        }
        if (is_array($size) && count($size) > 1) {
            $x = array_shift($size);
            $y = array_shift($size);
        } else {
            throw new \Exception('Wrong point given!');
        }
        $this->size = implode(':', [(int) $x, (int) $y]);

        return $this;
    }

    /**
     * @return ReportWidget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param ReportWidget $report
     *
     * @return $this
     */
    public function setWidget(ReportWidget $report = null)
    {
        $this->widget = $report;

        return $this;
    }

    /**
     * @return array
     */
    public function getHcData()
    {
        if (!is_null($this->hc_data) && !is_array($this->hc_data)) {
            $temp          = explode(':', $this->hc_data);
            $this->hc_data = [
                'inner_type' => $temp[1],
                'outer_type' => $temp[0],
            ];
        }

        return $this->hc_data;
    }

    /**
     * @param string $data
     *
     * @return $this
     */
    public function setHcData($data)
    {
        $this->hc_data = $data;

        return $this;
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getWidgetType()
    {
        return isset($this->widgetTypesMapping[$this->type]) ? $this->widgetTypesMapping[$this->type] : self::WIDGET_TYPE_TABLE;
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

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'    => 'report_dashboard_widget',
                'indexes' => [
                    'report_id_idx' => ['columns' => ['report_id']],
                    'widget_id_idx' => ['columns' => ['widget_id']],
                ],
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
                'fieldName'  => 'position',
                'type'       => 'string',
                'length'     => 5,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'position',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'size',
                'type'       => 'string',
                'length'     => 5,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'size',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'hc_data',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'hc_data',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'type',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'type',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'variables',
                'type'       => 'json_array',
                'length'     => 250,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'variables',
            ]
        );

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'widget',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportWidget',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'nullable'     => true,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'widget_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'report',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportDashboardReport',
                'mappedBy'     => null,
                'inversedBy'   => 'widgets',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'report_id',
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
