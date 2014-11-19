<?php

namespace Application\DeskPRO\NewSearch\Filter;

use Orb\Util\Arrays;
use Elastica\Filter;

class DepartmentFilter extends AbstractFilter
{
    public function getFilter()
    {
        $departments   = $this->person->getHelper('AgentPermissions')->getAllowedDepartments();
        $departmentIds = array();

        foreach ($departments as $department) {
            $departmentIds[] = (int)$department;
        }

        $departmentIds = Arrays::removeFalsey($departmentIds);

        if (!empty($departmentIds)) {
            $departmentIds = array_unique($departmentIds);
            $departmentIds = array_values($departmentIds);

            $filter = new Filter\Terms('department', $departmentIds);

            return $filter->toArray();
        } else {
            $filter = new Filter\Terms('department', array(-1));

            return $filter->toArray();
        }
    }
}
