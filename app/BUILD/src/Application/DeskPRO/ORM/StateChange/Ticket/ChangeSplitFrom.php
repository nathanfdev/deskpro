<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange\Ticket;

use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Application\DeskPRO\ORM\StateChange\NonStateTrackingInterface;

class ChangeSplitFrom implements ChangeInterface, NonStateTrackingInterface
{
    /**
     * @var string
     */
    private $field_id;

    /**
     * @var int
     */
    private $old_ticket_id;

    /**
     * @var array
     */
    private $message_ids;

    /**
     * @param $field_id
     * @param $old_ticket_id
     * @param array $message_ids
     */
    public function __construct($field_id, $old_ticket_id, array $message_ids = [])
    {
        $this->field_id      = $field_id;
        $this->old_ticket_id = $old_ticket_id;
        $this->message_ids   = $message_ids;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field_id;
    }

    /**
     * @return array
     */
    public function getOld()
    {
        return;
    }

    /**
     * @return array
     */
    public function getNew()
    {
        return [
            'field_id'      => $this->field_id,
            'old_ticket_id' => $this->old_ticket_id,
            'message_ids'   => $this->message_ids,
        ];
    }

    /**
     * @return array
     */
    public function getMessageIds()
    {
        return $this->message_ids;
    }

    /**
     * @return int
     */
    public function geOldTicketId()
    {
        return $this->old_ticket_id;
    }

    /**
     * @return bool
     */
    public function isSame()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isCollection()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isEntity()
    {
        return false;
    }
}
