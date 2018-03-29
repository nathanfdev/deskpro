<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange;

class ChangeSimple implements ChangeInterface
{
    /**
     * @var string
     */
    private $field_id;

    /**
     * @var mixed
     */
    private $old;

    /**
     * @var mixed
     */
    private $new;

    /**
     * @var bool
     */
    private $is_same = false;

    /**
     * @param string $field_id
     * @param mixed  $old
     * @param mixed  $new
     */
    public function __construct($field_id, $old = null, $new = null)
    {
        $this->field_id = $field_id;
        $this->old      = $old;
        $this->new      = $new;

        if ($old == $new) {
            if (is_null($old) && is_null($new)) {
                $this->is_same = true;
            } elseif (is_scalar($old) || is_scalar($new)) {
                if (is_numeric($old) && is_numeric($new)) {
                    if ($new == $old) {
                        $this->is_same = true;
                    }
                } else {
                    if ($new === $old) {
                        $this->is_same = true;
                    }
                }
            }
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
