<?php

namespace Application\DeskPRO\NewSearch\Filter;

class AssignmentFilter extends AbstractFilter
{
    public function getFilter()
    {
        $filter = null;

        if ($this->person->hasPerm('agent_tickets.view_others')) {
            $filter = array('range' => array('agent' => array('gte' => 0)));
        } else {
            if ($this->person->hasPerm('agent_tickets.view_unassigned')) {
                $filter = array('terms' => array('agent' => array(0, $this->person->getId())));
            } else {
                $filter = array('term' => array('agent' => $this->person->getId()));
            }
        }

        return $filter;
    }
}