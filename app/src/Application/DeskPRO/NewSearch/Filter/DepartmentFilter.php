<?php

namespace Application\DeskPRO\NewSearch\Filter;

class DepartmentFilter extends AbstractFilter
{
    public function getFilter()
    {
        $departments   = $this->person->getHelper('AgentPermissions')->getAllowedDepartments();
        $departmentIds = array();

        foreach ($departments as $department) {
			$departmentIds[] = (int) $department;
        }

		if (!empty($departmentIds)) {
			$departmentIds = array_unique($departmentIds);
			$filter = array('term' => array('department' => $departmentIds));
			return $filter;
		} else {
			return null;
		}
    }
} 