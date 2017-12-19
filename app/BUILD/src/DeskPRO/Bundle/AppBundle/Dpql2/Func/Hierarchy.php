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
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\Number;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\SelectPart;

/**
 * Handler for HIERARCHY function.
 */
class Hierarchy extends AbstractFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultHandler $result)
    {
        if ($section != 'group') {
            throw new Exception('HIERARCHY() may only be used in GROUP BY.');
        }
        if (!in_array(count($arguments), [2, 3])) {
            throw new Exception('HIERARCHY() can only accept 2 or 3 arguments.');
        }

        $expression = reset($arguments);
        $prepared   = $expression->prepare($statement, $section, $stack, $select, $result);

        $minDepth = null;
        if (array_key_exists(1, $arguments)) {
            if (!$arguments[1] instanceof Number) {
                throw new Exception('HIERARCHY() 2nd argument must be a number.');
            }
            $minDepth = $arguments[1]->getValue();
            if ($minDepth <= 0) {
                throw new Exception('HIERARCHY() 2nd argument must be a positive number.');
            }
        }

        $maxDepth = null;
        if (array_key_exists(2, $arguments)) {
            if (!$arguments[2] instanceof Number) {
                throw new Exception('HIERARCHY() 3rd argument must be a number.');
            }
            $maxDepth = $arguments[2]->getValue();
            if ($maxDepth <= 0) {
                throw new Exception('HIERARCHY() 3rd argument must be a positive number.');
            }
        }

        if ($minDepth && !$maxDepth) {
            $maxDepth = $minDepth;
        }

        if ($minDepth > $maxDepth) {
            throw new Exception('HIERARCHY() 3rd argument must be bigger or equal than the 2nd.');
        }

        $hierarchyPlugin = $statement->getSqlSelectContext()->getHierarchyPlugin();
        $hierarchyPlugin->setHierarchyMinDepth($minDepth - 1);
        $hierarchyPlugin->setHierarchyMaxDepth($maxDepth - 1);

        return $prepared;
    }
}
