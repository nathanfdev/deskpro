<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Val;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class VarVal extends ComputedVal
{
    const VAL_TYPE = Query::VAL_VAR;

    /**
     * @var string
     */
    public $identity = '';

    /**
     * @param string $identity
     */
    public function __construct($identity)
    {
        $this->identity = $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'valueType' => self::VAL_TYPE,
            'identity'  => $this->identity,
            'tokenPos'  => $this->tokenPos,
        ];
    }

    /**
     * @param array $props
     *
     * @return VarVal
     */
    public static function fromArray(array $props)
    {
        if ($props['valueType'] !== static::VAL_TYPE) {
            throw new \InvalidArgumentException(sprintf('Expected valueType of %s', static::VAL_TYPE));
        }

        $o = new self($props['identity'], $params);
        if (!empty($props['tokenPos'])) {
            $o->tokenPos = $props['tokenPos'];
        }

        return $o;
    }
}
