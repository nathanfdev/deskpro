<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\TicketLayout\LayoutFieldCriteria as BaseLayoutFieldCriteria;
use JMS\Serializer\Annotation as JMS;

/**
 * Class LayoutFieldCriteria.
 */
class LayoutFieldCriteria
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $mode;

    /**
     * @var array
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LayoutFieldCriteriaTerm>")
     */
    private $terms = [];

    /**
     * Constructor.
     *
     * @param BaseLayoutFieldCriteria $criteria
     */
    public function __construct(BaseLayoutFieldCriteria $criteria)
    {
        $this->mode = $criteria->getMode();

        foreach ($criteria->getTerms() as $term) {
            $this->terms[] = new LayoutFieldCriteriaTerm($term);
        }
    }
}
