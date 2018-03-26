<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\TicketLayout\LayoutField as BaseLayoutField;
use JMS\Serializer\Annotation as JMS;

/**
 * Class LayoutFieldOptions.
 */
class LayoutFieldOptions
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $onNewticket;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $onViewticket;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $onViewticketMode;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $onEditticket;

    /**
     * @var \Application\DeskPRO\TicketLayout\LayoutFieldCriteria
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LayoutFieldCriteria")
     */
    private $criteria;

    /**
     * Constructor.
     *
     * @param BaseLayoutField $layoutField
     */
    public function __construct(BaseLayoutField $layoutField)
    {
        $this->onNewticket      = $layoutField->isVisibleOnView();
        $this->onViewticket     = $layoutField->isVisibleOnView();
        $this->onViewticketMode = $layoutField->getOnViewticketMode();
        $this->onEditticket     = $layoutField->isVisibleOnEdit();

        if ($layoutField->getCriteria()) {
            $this->criteria = new LayoutFieldCriteria($layoutField->getCriteria());
        }
    }
}
