<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Component\Util\ListUtils;

class CustomFieldsTermsHandler extends AbstractTermsHandler
{
    /**
     * @var CustomField[]
     */
    private $customTicketFields = [];

    /**
     * @var CustomField[]
     */
    private $customPersonFields = [];

    /**
     * Terms constructor.
     *
     * @param CustomField[] $customTicketFields
     * @param CustomField[] $customPersonFields
     */
    public function __construct(array $customTicketFields = [], array $customPersonFields = [])
    {
        $this->customTicketFields = $customTicketFields;
        $this->customPersonFields = $customPersonFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return array_merge(
            ListUtils::map($this->customTicketFields, function ($f) {
                return sprintf(Terms::TICKET_CUSTOM, $f->field);
            }),
            ListUtils::map($this->customPersonFields, function ($f) {
                return sprintf(Terms::PERSON_CUSTOM, $f->field);
            })
        );
    }
}
