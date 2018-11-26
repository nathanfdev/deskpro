<?php

namespace DeskPRO\Bundle\ReportBundle\Serializer\Model;

use Application\DeskPRO\Entity\ReportDashboardWidget as ReportDashboardWidgetEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\InlineCustomSideload;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ReportWidget.
 */
class ReportDashboardWidget
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Dashboard report.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\DashboardReport>")
     *
     * @var ReportWidget
     */
    private $report;

    /**
     * Reference to ReportWidget Entity that holds DPQL.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\ReportWidget>")
     *
     * @var ReportWidget
     */
    private $widget;

    /**
     * Widget title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * Widget type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * Widget type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $widgetType;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $row;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $col;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $sizeX;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $sizeY;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $widgetVariables;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $options;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $jsCode;

    /**
     * @JMS\Type("raw")
     *
     * @var InlineCustomSideload
     */
    private $renderedResult;

    /**
     * Constructor.
     *
     * @param ReportDashboardWidgetEntity $entity
     */
    public function __construct(ReportDashboardWidgetEntity $entity)
    {
        $this->id              = $entity->getId();
        $this->report          = $entity->getReport();
        $this->type            = $entity->getType();
        $this->widget          = $entity->getWidget();
        $this->title           = $entity->getTitle();
        $this->row             = $entity->getRow();
        $this->col             = $entity->getCol();
        $this->sizeX           = $entity->getSizeX();
        $this->sizeY           = $entity->getSizeY();
        $this->widgetVariables = $entity->getVariables() ?: [];
        $this->options         = $entity->getOptions();
        $this->jsCode          = $entity->getJsCode();
    }

    /**
     * @param string $widgetType
     */
    public function setWidgetType($widgetType)
    {
        $this->widgetType = $widgetType;
    }

    /**
     * @param InlineCustomSideload $renderedResult
     */
    public function setRenderedResult($renderedResult)
    {
        $this->renderedResult = $renderedResult;
    }
}
