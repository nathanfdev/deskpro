<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Statement\Display;

/**
 * Handler for HIERARCHY function.
 */
class Hierarchy extends AbstractFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(
        Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
    ) {
        if ($section != 'group') {
            throw new Exception('HIERARCHY() may only be used in GROUP BY.');
        }
        if (!in_array(count($this->_arguments), [2, 3])) {
            throw new Exception('HIERARCHY() can only accept 2 or 3 arguments.');
        }

        $expression = reset($this->_arguments);
        $prepared   = $expression->prepare($statement, $section, $stack, $select, $result);

        $minDepth = null;
        if (array_key_exists(1, $this->_arguments)) {
            if (!$this->_arguments[1] instanceof Dpql\Statement\Part\Number) {
                throw new Exception('HIERARCHY() 2nd argument must be a number.');
            }
            $minDepth = $this->_arguments[1]->getValue();
            if ($minDepth <= 0) {
                throw new Exception('HIERARCHY() 2nd argument must be a positive number.');
            }
        }

        $maxDepth = null;
        if (array_key_exists(2, $this->_arguments)) {
            if (!$this->_arguments[2] instanceof Dpql\Statement\Part\Number) {
                throw new Exception('HIERARCHY() 3rd argument must be a number.');
            }
            $maxDepth = $this->_arguments[2]->getValue();
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
