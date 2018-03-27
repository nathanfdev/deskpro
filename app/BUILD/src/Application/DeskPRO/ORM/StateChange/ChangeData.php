<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange;

class ChangeData implements ChangeInterface
{
    /**
     * @var string
     */
    private $field_id;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string $field_id
     * @param mixed  $old
     * @param mixed  $new
     */
    public function __construct($field_id, array $data = [])
    {
        $this->field_id = $field_id;
        $this->data     = $data;
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
        return;
    }

    /**
     * @return mixed
     */
    public function getNew()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
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
