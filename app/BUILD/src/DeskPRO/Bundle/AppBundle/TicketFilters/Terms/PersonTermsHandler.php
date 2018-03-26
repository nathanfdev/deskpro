<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

class PersonTermsHandler extends AbstractTermsHandler
{
    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return [
            Terms::PERSON_ID,
            Terms::PERSON_LABELS,
            Terms::PERSON_USERGROUPS,
        ];
    }
}
