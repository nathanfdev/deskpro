<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange;

class ChangeDate implements ChangeInterface
{
    /**
     * @var string
     */
    private $field_id;

    /**
     * @var \DateTime
     */
    private $old;

    /**
     * @var \DateTime
     */
    private $new;

    /**
     * @var bool
     */
    private $is_same = false;

    /**
     * @param string    $field_id
     * @param \DateTime $old
     * @param \DateTime $new
     */
    public function __construct($field_id, \DateTime $old = null, \DateTime $new = null)
    {
        $this->field_id = $field_id;
        $this->old      = $old;
        $this->new      = $new;

        // Check for nulls or if they are the same date
        if ($old === $new || $old == $new) {
            $this->is_same = true;
        }
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field_id;
    }

    /**
     * @return mixed
     */
    public function getOld()
    {
        return $this->old;
    }

    /**
     * @return mixed
     */
    public function getNew()
    {
        return $this->new;
    }

    /**
     * @return bool
     */
    public function isSame()
    {
        return $this->is_same;
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
