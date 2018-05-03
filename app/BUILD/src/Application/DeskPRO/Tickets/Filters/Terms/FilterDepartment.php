<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Filters based on set department.
 *
 * @option int[] department_ids
 */
class FilterDepartment extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('department_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $options = $this->getTermOptions();

        return $this->getIdMatchQuery('tickets.department_id', $options->get('department_ids'));
    }
}
