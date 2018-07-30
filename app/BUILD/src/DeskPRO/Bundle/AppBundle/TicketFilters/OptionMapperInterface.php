<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

interface OptionMapperInterface
{
    public function getValue($fieldId, $value);

    public function getValueById($fieldId, $valueId);
}
