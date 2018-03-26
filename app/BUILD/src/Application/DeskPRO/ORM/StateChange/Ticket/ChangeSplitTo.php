<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange\Ticket;

use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Application\DeskPRO\ORM\StateChange\NonStateTrackingInterface;

class ChangeSplitTo implements ChangeInterface, NonStateTrackingInterface
{
    /**
     * @var string
     */
    private $field_id;

    /**
     * @var int
     */
    private $new_ticket_id;

    /**
     * @var array
     */
    private $message_ids;

    /**
     * @param $field_id
     * @param $new_ticket_id
     * @param array $message_ids
     */
    public function __construct($field_id, $new_ticket_id, array $message_ids = [])
    {
        $this->field_id      = $field_id;
        $this->new_ticket_id = $new_ticket_id;
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
            'new_ticket_id' => $this->new_ticket_id,
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
    public function getNewTicketId()
    {
        return $this->new_ticket_id;
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
