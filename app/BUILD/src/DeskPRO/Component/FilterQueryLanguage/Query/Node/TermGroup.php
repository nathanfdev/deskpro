<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Node;

use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\GroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;

/**
 * Class TermGroup.
 */
class TermGroup extends Node
{
    const TYPE = Query::NODE_TERM_GROUP;

    /**
     * @var GroupOp
     */
    public $operator;

    /**
     * @var Term|Node[]
     */
    public $terms = [];

    /**
     * TermGroup constructor.
     *
     * @param GroupOp $operator
     * @param Node[]  $terms
     */
    public function __construct(GroupOp $operator, array $terms)
    {
        $this->operator = $operator;
        $this->terms    = $terms;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $terms = [];
        foreach ($this->terms as $t) {
            $terms[] = $t->toArray();
        }

        return [
            'type'     => self::TYPE,
            'operator' => $this->operator->getOperator(),
            'terms'    => $terms,
            'tokenPos' => $this->tokenPos,
        ];
    }

    /**
     * @param array $props
     *
     * @return TermGroup
     */
    public static function fromArray(array $props)
    {
        if ($props['type'] !== self::TYPE) {
            throw new \InvalidArgumentException('Expected TERM_GROUP type');
        }

        $terms = [];
        foreach ($props['terms'] as $t) {
            if ($t['type'] === Query::NODE_TERM_GROUP) {
                $terms[] = self::fromArray($t);
            } else {
                $terms[] = Term::fromArray($t);
            }
        }

        $o = new self(
            GroupOp::createGroupOp($props['operator']),
            $terms
        );
        if (!empty($props['tokenPos'])) {
            $o->tokenPos = $props['tokenPos'];
        }

        return $o;
    }
}
