<?php

namespace Application\DeskPRO\NewSearch\Filter;

use Orb\Util\Arrays;

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
			$filter = array('term' => array('department' => $departmentIds));
			return $filter;
		} else {
			return null;
		}
    }
} 