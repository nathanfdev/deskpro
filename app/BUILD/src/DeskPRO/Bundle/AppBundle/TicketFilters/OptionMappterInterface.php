<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

interface OptionMappterInterface
{
    public function getValue($fieldId, $value);

    public function getValueById($fieldId, $valueId);
}
