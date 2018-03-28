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
