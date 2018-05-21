<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomField;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\StringUtils;

class CustomFieldSet
{
    /**
     * @var CustomField[]
     */
    public $customTicketFields = [];

    /**
     * @var CustomField[]
     */
    public $customPersonFields = [];

    /**
     * @var CustomField[]
     */
    public $customOrgFields = [];

    /**
     * CustomFieldSet constructor.
     *
     * @param CustomField[] $customTicketFields
     * @param CustomField[] $customPersonFields
     * @param CustomField[] $customOrgFields
     */
    public function __construct(array $customTicketFields = [], array $customPersonFields = [], array $customOrgFields = [])
    {
        $this->customTicketFields = $customTicketFields;
        $this->customPersonFields = $customPersonFields;
        $this->customOrgFields    = $customOrgFields;
    }

    /**
     * @param string $fieldId
     *
     * @return bool
     */
    public function isCustomFieldId($fieldId)
    {
        list($termType) = $this->parseCustomFieldId($fieldId);

        return $termType !== null;
    }

    /**
     * Given an ID, check if it's a custom field.
     *
     * @param string $fiel$fieldIddIdRef
     *
     * @return array [termType, customFieldRef] if not found, then values will be null
     */
    public function parseCustomFieldId($fieldId)
    {
        if ($customFieldRef = StringUtils::removeFromStart(TermFieldIds::TICKET_CUSTOM.'.', $fieldId)) {
            return [TermFieldIds::TICKET_CUSTOM, $customFieldRef];
        } elseif ($customFieldRef = StringUtils::removeFromStart(TermFieldIds::PERSON_CUSTOM.'.', $fieldId)) {
            return [TermFieldIds::PERSON_CUSTOM, $customFieldRef];
        } elseif ($customFieldRef = StringUtils::removeFromStart(TermFieldIds::ORG_CUSTOM.'.', $fieldId)) {
            return [TermFieldIds::ORG_CUSTOM, $customFieldRef];
        } else {
            return [null, null];
        }
    }

    /**
     * @param string $fieldIdRef
     *
     * @return [fieldId, field, termType]
     */
    public function getFieldInfoById($fieldId)
    {
        list($termType, $customFieldRef) = $this->parseCustomFieldId($fieldId);

        /** @var CustomField $field */
        $field = ListUtils::first($this->getFieldCollectionByType($termType), function (CustomField $f) use ($customFieldRef) {
            return $f->field == $customFieldRef || in_array($customFieldRef, $f->aliases);
        });

        if (!$field) {
            throw new \InvalidArgumentException('Unknown field '.$customFieldRef);
        }

        return [
            $termType.'.'.$field->field,
            $field,
            $termType,
        ];
    }

    /**
     * @param string $termType
     *
     * @return CustomField[]
     */
    public function getFieldCollectionByType($termType)
    {
        switch ($termType) {
            case TermFieldIds::TICKET_CUSTOM:
                return $this->customTicketFields;
            case TermFieldIds::PERSON_CUSTOM:
                return $this->customPersonFields;
            case TermFieldIds::ORG_CUSTOM:
                return $this->customTicketFields;
            default:
                throw new \InvalidArgumentException('Unknown term type');
        }
    }

    /**
     * Given a field ID that may be referenced using an alias, unwind the alias into the real
     * field id. e.g. `ticket.data.foobar` -> `ticket.data.22`.
     *
     * @param string $fieldId
     *
     * @return string
     */
    public function canonicalizeFieldId($fieldId)
    {
        list($realFieldId) = $this->getFieldInfoById($fieldId);

        return $realFieldId;
    }
}
