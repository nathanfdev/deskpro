<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketFilterTerm.
 */
class TicketFilterTerm
{
    /**
     * Term type.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $type;

    /**
     * Term operator.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $op;

    /**
     * Term options.
     *
     * @var array
     */
    private $options;

    /**
     * Constructor.
     *
     * @param array $term
     */
    public function __construct(array $term)
    {
        $this->type    = $term['type'];
        $this->op      = $term['op'];
        $this->options = $term['options'] ?: new \ArrayObject();
    }
}
