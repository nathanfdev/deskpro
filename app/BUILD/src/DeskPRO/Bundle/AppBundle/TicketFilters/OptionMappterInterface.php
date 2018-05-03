<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

interface OptionMappterInterface
{
    public function getValue($fieldId, $value);
}
