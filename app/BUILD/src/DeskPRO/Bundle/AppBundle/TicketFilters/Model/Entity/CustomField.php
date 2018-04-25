<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity;

use Application\DeskPRO\Entity\CustomDefAbstract;

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
     * See CustomDefAbstract::TYPE_*.
     *
     * @var string
     */
    public $type = null;

    /**
     * @var string[]
     */
    public $aliases = [];

    /**
     * CustomField constructor.
     *
     * @param int      $field
     * @param string   $type
     * @param string[] $aliases
     */
    public function __construct($field = 0, $type = null, array $aliases = [])
    {
        $this->field   = $field;
        $this->type    = $type;
        $this->aliases = $aliases;
    }

    /**
     * Is the field suitable for grouping on?
     *
     * @return bool
     */
    public function isGroupingCapable()
    {
        switch ($this->type) {
            case CustomDefAbstract::TYPE_CHOICE: return true;
        }

        return false;
    }
}
