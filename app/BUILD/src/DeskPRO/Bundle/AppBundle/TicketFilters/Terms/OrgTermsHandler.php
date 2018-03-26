<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

class OrgTermsHandler extends AbstractTermsHandler
{
    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return [
            Terms::ORG_ID,
            Terms::ORG_LABELS,
            Terms::ORG_USERGROUPS,
        ];
    }
}
