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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Reports;

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
     * Constructor.
     *
     * @param ReportWidgetEntity $entity
     * @param array              $queryParts
     * @param array              $translatedLabels
     */
    public function __construct(ReportWidgetEntity $entity, array $queryParts, array $translatedLabels)
    {
        $this->id           = $entity->getId();
        $this->uniqueKey    = $entity->getUniqueKey();
        $this->title        = $entity->getTitle();
        $this->description  = $entity->getDescription();
        $this->query        = $entity->getQuery();
        $this->queryParts   = $queryParts;
        $this->parent       = $entity->getParent();
        $this->isCustom     = $entity->isCustom();
        $this->labels       = $translatedLabels;
        $this->displayOrder = $entity->getDisplayOrder();
        $this->displayTypes = $entity->getDisplayTypes();
        $this->variables    = $entity->getVariables();
    }

    /**
     * @param InlineCustomSideload $renderedResult
     */
    public function setRenderedResult($renderedResult)
    {
        $this->renderedResult = $renderedResult;
    }
}
