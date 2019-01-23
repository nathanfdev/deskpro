<?php

namespace DeskPRO\Bundle\ReportBundle\Serializer\Model;

use Application\DeskPRO\Entity\ReportWidget as ReportWidgetEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\InlineCustomSideload;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ReportWidget.
 */
class ReportWidget
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     */
    private $uniqueKey;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $description;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $query;

    /**
     * @var array
     *
     * @JMS\Type("array")
     */
    private $queryParts;

    /**
     * @var \Application\DeskPRO\Entity\ReportWidget
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\ReportWidget>")
     */
    private $parent;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $isCustom;

    /**
     * @var array
     *
     * @JMS\Type("array<string>")
     */
    private $labels;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $displayOrder;

    /**
     * @var array
     *
     * @JMS\Type("array")
     */
    private $displayTypes;

    /**
     * @var array
     *
     * @JMS\Type("array")
     */
    private $variables;

    /**
     * @JMS\Type("raw")
     *
     * @var InlineCustomSideload
     */
    private $renderedResult;

    /**
     * Indicates if this widget allowed to be changed with parts-build-form.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $extendedQuery;

    /**
     * @JMS\Type("raw")
     *
     * @var array
     */
    private $reports;

    /**
     * Constructor.
     *
     * @param ReportWidgetEntity $entity
     * @param array              $queryParts
     * @param array              $translatedLabels
     * @param bool               $extendedQuery
     */
    public function __construct(
        ReportWidgetEntity $entity,
        array $queryParts,
        array $translatedLabels,
        $extendedQuery = false
    ) {
        $this->id            = $entity->getId();
        $this->uniqueKey     = $entity->getUniqueKey();
        $this->title         = $entity->getTitle();
        $this->description   = $entity->getDescription();
        $this->query         = $entity->getQuery();
        $this->queryParts    = $queryParts;
        $this->parent        = $entity->getParent();
        $this->isCustom      = $entity->isCustom();
        $this->labels        = $translatedLabels;
        $this->displayOrder  = $entity->getDisplayOrder();
        $this->displayTypes  = $entity->getDisplayTypes();
        $this->variables     = $entity->getVariables();
        $this->extendedQuery = $extendedQuery;
    }

    /**
     * @param InlineCustomSideload $renderedResult
     */
    public function setRenderedResult($renderedResult)
    {
        $this->renderedResult = $renderedResult;
    }

    /**
     * @param InlineCustomSideload $reports
     */
    public function setReports($reports)
    {
        $this->reports = $reports;
    }
}
