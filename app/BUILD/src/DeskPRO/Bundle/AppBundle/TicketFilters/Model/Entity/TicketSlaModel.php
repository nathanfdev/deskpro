<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity;

class TicketSlaModel
{
    /**
     * @var int
     */
    public $sla_id;

    /***
     * ok, warning, fail
     * @var string
     */
    public $status;

    /**
     * @var \DateTime|null
     */
    public $warn_date;

    /**
     * @var \DateTime|null
     */
    public $fail_date;

    /**
     * @var bool
     */
    public $is_completed = false;

    /**
     * @param TicketSlaModel $other
     *
     * @return array
     */
    public function getChangedFields(TicketSlaModel $other)
    {
        $changed = [];

        foreach ([
            'status', 'warn_date', 'fail_date', 'is_completed',
        ] as $simpleField) {
            if ($this->$simpleField != $other->$simpleField) {
                $changed[] = "ticket.slas[{$this->id}].$simpleField";
            }
        }

        return $changed;
    }
}
