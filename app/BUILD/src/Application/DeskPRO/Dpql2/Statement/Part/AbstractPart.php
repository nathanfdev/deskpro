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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql2\Statement\Part;

use Application\DeskPRO\Dpql2;
use Application\DeskPRO\Dpql2\Statement\SelectPart;

/**
 * Abstract base for parts of a DPQL statement.
 */
abstract class AbstractPart
{
    /**
     * Prepares a part for use, including validating that the usage is valid.
     *
     * @param \Application\DeskPRO\Dpql2\Statement\SelectPart          $statement
     * @param string                                                   $section   Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart[] $stack     Parent parts
     * @param \Application\DeskPRO\Dpql2\SqlSelect                     $select    Select being built up
     * @param \Application\DeskPRO\Dpql2\ResultHandler                 $result
     *
     * @throws \Application\DeskPRO\Dpql2\Exception
     *
     * @return \Application\DeskPRO\Dpql2\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    abstract public function prepare(
        SelectPart $statement, $section, array $stack, Dpql2\SqlSelect $select, Dpql2\ResultHandler $result
    );

    /**
     * Renders a part back to DPQL.
     *
     * @param \Application\DeskPRO\Dpql2\Statement\SelectPart          $statement
     * @param string                                                   $section
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart[] $stack
     *
     * @return string
     */
    abstract public function toDpql(SelectPart $statement, $section, array $stack);

    /**
     * Gets the part stack to pass to a child part (includes this object).
     *
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart[] $stack
     *
     * @return \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart[]
     */
    public function getChildStack(array $stack)
    {
        $childStack = $stack;
        array_unshift($childStack, $this);

        return $childStack;
    }
}
