<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange\Ticket;

use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Application\DeskPRO\ORM\StateChange\NonStateTrackingInterface;

class ChangeMerge implements ChangeInterface, NonStateTrackingInterface
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
    private $lost_data;

    /**
     * @param string $field_id
     * @param int    $old_ticket_id
     * @param array  $lost_data
     */
    public function __construct($field_id, $old_ticket_id, array $lost_data = [])
    {
        $this->field_id      = $field_id;
        $this->old_ticket_id = $old_ticket_id;
        $this->lost_data     = $lost_data;
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
            'lost_data'     => $this->lost_data,
        ];
    }

    /**
     * @return array
     */
    public function getLostData()
    {
        return $this->lost_data;
    }

    /**
     * @param array $lost_data
     */
    public function setLostData(array $lost_data)
    {
        $this->lost_data = $lost_data;
    }

    /**
     * @return int
     */
    public function getOldTicketId()
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
