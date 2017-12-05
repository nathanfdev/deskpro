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
