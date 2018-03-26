<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity;

class CustomField
{
    /**
     * The field ID the value is for.
     *
     * @var int
     */
    public $field = 0;

    /**
     * The deskpro field type (e.g. choice, text, etc).
     *
     * @var string
     */
    public $type = null;

    /**
     * The filter data type. E.g. STRING or INT[].
     *
     * @var string
     */
    public $valueType;
}
