<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Val;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class FuncVal extends ComputedVal
{
    const VAL_TYPE = Query::VAL_FUNC;

    /**
     * @var string
     */
    public $name;

    /**
     * @var Val[]
     */
    public $params = [];

    /**
     * FuncVal constructor.
     *
     * @param string $name
     * @param Val[]  $params
     */
    public function __construct($name, array $params = [])
    {
        $this->name   = $name;
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $params = [];
        foreach ($this->params as $p) {
            $params[] = $p->toArray();
        }

        return [
            'valueType' => self::VAL_TYPE,
            'name'      => $this->name,
            'params'    => $params,
            'tokenPos'  => $this->tokenPos,
        ];
    }

    /**
     * @param array $props
     *
     * @return FuncVal
     */
    public static function fromArray(array $props)
    {
        if ($props['valueType'] !== static::VAL_TYPE) {
            throw new \InvalidArgumentException(sprintf('Expected valueType of %s', static::VAL_TYPE));
        }

        $params = [];
        foreach ($props['params'] as $p) {
            $params[] = Val::fromArray($p);
        }

        $o = new self($props['name'], $params);
        if (!empty($props['tokenPos'])) {
            $o->tokenPos = $props['tokenPos'];
        }

        return $o;
    }
}
