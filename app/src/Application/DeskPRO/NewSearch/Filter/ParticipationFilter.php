<?php

namespace Application\DeskPRO\NewSearch\Filter;

class ParticipationFilter extends AbstractFilter
{
    public function getFilter()
    {
        $filter = array('term' => array('participants' => $this->person->getId()));
        return $filter;
    }
} 