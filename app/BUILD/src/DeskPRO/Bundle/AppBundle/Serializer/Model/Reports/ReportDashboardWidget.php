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
        $this->id    = $entity->getId();
        $this->type  = $entity->getWidgetType();
        $this->title = $entity->getTitle();
        $this->row   = $entity->getPosition()[0];
        $this->col   = $entity->getPosition()[1];
        $this->sizeX = $entity->getSize()[0];
        $this->sizeY = $entity->getSize()[1];
    }

    /**
     * @param InlineCustomSideload $renderedResult
     */
    public function setRenderedResult($renderedResult)
    {
        $this->renderedResult = $renderedResult;
    }
}
