<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query;

class Field extends QueryPart
{
    /**
     * @var strng
     */
    public $identity;

    /**
     * @param strng $identity
     */
    public function __construct($identity)
    {
        $this->identity = $identity;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return ['identity' => $this->identity];
    }

    /**
     * @param array $props
     *
     * @return Field
     */
    public static function fromArray(array $props)
    {
        return new self($props['identity']);
    }
}
