<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange;

class ChangeTriggerLog implements ChangeInterface, NonStateTrackingInterface
{
    /**
     * @var string
     */
    private $field_id;

    /**
     * @var string
     */
    private $trigger_id;

    /**
     * @var string
     */
    private $trigger_title;

    /**
     * @param $field_id
     * @param $trigger_id
     * @param $trigger_title
     */
    public function __construct($field_id, $trigger_id, $trigger_title)
    {
        $this->field_id      = $field_id;
        $this->trigger_id    = $trigger_id;
        $this->trigger_title = $trigger_title;
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
            'trigger_id'    => $this->trigger_id,
            'trigger_title' => $this->trigger_title,
        ];
    }

    /**
     * @return string
     */
    public function getTriggerId()
    {
        return $this->trigger_id;
    }

    /**
     * @return string
     */
    public function getTriggerTitle()
    {
        return $this->trigger_title;
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
