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

namespace DeskPRO\Bundle\AppBundle\Dpql2\Func;

use DeskPRO\Bundle\AppBundle\Dpql2\Exception;
use DeskPRO\Bundle\AppBundle\Dpql2\ResultHandler;
use DeskPRO\Bundle\AppBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\SelectPart;

/**
 * This is used in SPLIT/GROUP BY clauses to ensure that the printed value
 * can be different than the split value. For example, PRINT(tickets.subject, tickets.id).
 */
class Printable extends AbstractFunc
{
    /**
     * {@inheritdoc}
     */
    public static function getName()
    {
        return 'PRINT';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultHandler $result)
    {
        if (!in_array($section, ['split', 'group'])) {
            throw new Exception('PRINT() may only be used in SPLIT BY and GROUP BY sections.');
        }

        if (count($arguments) != 2) {
            throw new Exception('PRINT() can only accept 2 arguments');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        /** @var AbstractPart $sql */
        /** @var AbstractPart $print */
        $sql   = reset($arguments);
        $print = next($arguments);

        $printPrepped = $print->prepare($statement, $section, $childStack, $select, $result);
        $sqlPrepped   = $sql->prepare($statement, $section, $childStack, $select, $result);

        return new Prepared(
            $sqlPrepped->sql(), $printPrepped->name(), $printPrepped->printed(), $printPrepped->renderer()
        );
    }
}
