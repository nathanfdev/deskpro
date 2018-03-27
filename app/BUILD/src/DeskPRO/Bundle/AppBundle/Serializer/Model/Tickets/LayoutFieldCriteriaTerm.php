<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * Class LayoutFieldCriteriaTerm.
 */
class LayoutFieldCriteriaTerm
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $type;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $op;

    /**
     * @var array
     *
     * @JMS\Type("array")
     */
    private $options;

    /**
     * Constructor.
     *
     * @param CriteriaTermInterface $term
     */
    public function __construct(CriteriaTermInterface $term)
    {
        $this->type    = $term->getTermType();
        $this->op      = $term->getTermOperator();
        $this->options = $term->getTermOptions();
    }
}
