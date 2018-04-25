<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Node;

use DeskPRO\Component\FilterQueryLanguage\Query\Field;
use DeskPRO\Component\FilterQueryLanguage\Query\Op\Op;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\Opt;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class Term extends Node
{
    const TYPE = Query::NODE_TERM;

    /**
     * @var Op
     */
    public $operator;

    /**
     * @var Field
     */
    public $field;

    /**
     * @var array|Opt
     */
    public $options;

    /**
     * @param Op    $operator
     * @param Field $field
     * @param Opt   $options
     */
    public function __construct(Op $operator, Field $field, Opt $options)
    {
        $this->operator = $operator;
        $this->field    = $field;
        $this->options  = $options;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'type'     => self::TYPE,
            'field'    => $this->field->toArray(),
            'operator' => $this->operator->getOperator(),
            'options'  => $this->options->toArray(),
            'tokenPos' => $this->tokenPos,
        ];
    }

    /**
     * @param array $props
     *
     * @return Term
     */
    public static function fromArray(array $props)
    {
        if ($props['type'] !== self::TYPE) {
            throw new \InvalidArgumentException('Expected TERM type');
        }

        $o = new self(
            Op::createOp($props['operator']),
            Field::fromArray($props['field']),
            Opt::fromArray($props['options'])
        );
        if (!empty($props['tokenPos'])) {
            $o->tokenPos = $props['tokenPos'];
        }

        return $o;
    }
}
