<?php

namespace DeskPRO\Component\FilterQueryLanguage;

use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\BetweenOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\CompareOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\InOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\FuncVal;

/**
 * Use this iterator to iterate over values of a term. Useful when you need to know
 * what sorts of values are being used.
 */
class ValueIterator extends \ArrayIterator implements \RecursiveIterator
{
    public function __construct(Term $term)
    {
        $options = $term->options;

        switch (true) {
            case $options instanceof BetweenOpt:
                parent::__construct([$options->value1, $options->value2]);
                break;

            case $options instanceof CompareOpt:
                parent::__construct([$options->value]);
                break;

            case $options instanceof InOpt:
                parent::__construct($options->valueList);
                break;

            default:
                parent::__construct([]);
        }
    }

    public function hasChildren()
    {
        $c = $this->current();
        switch (true) {
            case $c instanceof FuncVal:
                return !empty($c->params);

            default:
                return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        $c = $this->current();
        switch (true) {
            case $c instanceof FuncVal:
                return $c->params;

            default:
                return [];
        }
    }
}
