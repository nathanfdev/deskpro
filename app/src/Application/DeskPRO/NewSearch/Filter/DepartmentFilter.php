<?php

namespace Application\DeskPRO\NewSearch\Filter;

class DepartmentFilter extends AbstractFilter
{
    public function getFilter()
    {
        $departments   = $this->person->getHelper('AgentPermissions')->getAllowedDepartments();
        $departmentIds = array();

        foreach ($departments as $department) {
            $departmentId = (int) $department;
            if (!in_array($departmentId, $departmentIds)) {
                $departmentIds[] = $departmentId;
            }
        }

        $filter = array('term' => array('department' => $departmentIds));
        return $filter;
    }
} 