<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Val;

class ScalarVal extends Val
{
    const VAL_TYPE = '';

    /**
     * @var mixed
     */
    public $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'valueType' => static::VAL_TYPE,
            'value'     => $this->value,
            'tokenPos'  => $this->tokenPos,
        ];
    }

    /**
     * @param array $props
     *
     * @return ScalarVal
     */
    public static function fromArray(array $props)
    {
        if ($props['valueType'] !== static::VAL_TYPE) {
            throw new \InvalidArgumentException(sprintf('Expected valueType of %s', static::VAL_TYPE));
        }

        $o = new self($props['value']);
        if (!empty($props['tokenPos'])) {
            $o->tokenPos = $props['tokenPos'];
        }

        return $o;
    }
}
