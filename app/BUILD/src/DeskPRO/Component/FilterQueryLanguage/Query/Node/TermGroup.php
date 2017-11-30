<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Component\FilterQueryLanguage\Query\Node;

use DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp\GroupOp;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class TermGroup extends Node
{
    const TYPE = Query::NODE_TERM_GROUP;

    /**
     * @var GroupOp
     */
    public $operator;

    /**
     * @var Node[]
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
