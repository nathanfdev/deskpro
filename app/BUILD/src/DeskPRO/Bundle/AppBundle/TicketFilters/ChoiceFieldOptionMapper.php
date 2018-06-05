<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\EntityRepository\CustomDefAbstract as CustomDefAbstractRepos;

class ChoiceFieldOptionMapper implements OptionMappterInterface
{
    /**
     * @var CustomFieldSet
     */
    private $customFieldSet;

    /**
     * @var CustomDefAbstractRepos
     */
    private $ticketFieldRepos;

    /**
     * ChoiceFieldOptionMapper constructor.
     *
     * @param CustomFieldSet         $customFieldSet
     * @param CustomDefAbstractRepos $ticketFieldRepos
     */
    public function __construct(
        CustomFieldSet $customFieldSet,
        CustomDefAbstractRepos $ticketFieldRepos
    ) {
        $this->customFieldSet   = $customFieldSet;
        $this->ticketFieldRepos = $ticketFieldRepos;
    }

    /**
     * @param string $fieldId
     * @param string $value
     *
     * @return int
     */
    public function getValue($fieldId, $value)
    {
        $findTitle = $this->normalizeString($value);

        list($type, $customFieldId) = $this->customFieldSet->parseCustomFieldId($fieldId);

        switch ($type) {
            case TermFieldIds::TICKET_CUSTOM:
                $repos = $this->ticketFieldRepos;
                break;

            default:
                return null;
        }

        /** @var CustomDefAbstract $field */
        $field = $repos->find($customFieldId);
        if (!$field) {
            return null;
        }

        foreach ($field->getChildren() as $c) {
            if ($findTitle === $this->normalizeString($c->getTitle())) {
                return $c->getId();
            }
        }

        return null;
    }

    /**
     * @param $fieldId
     * @param $valueId
     *
     * @return array|null
     */
    public function getValueById($fieldId, $valueId)
    {
        list($type, $customFieldId) = $this->customFieldSet->parseCustomFieldId($fieldId);

        switch ($type) {
            case TermFieldIds::TICKET_CUSTOM:
                $repos = $this->ticketFieldRepos;
                break;

            default:
                return null;
        }

        /** @var CustomDefAbstract $field */
        $field = $repos->find($customFieldId);
        if (!$field) {
            return null;
        }

        foreach ($field->getChildren() as $c) {
            if ($c->getId() == $valueId || $c->hasAlias($valueId)) {
                return array_unique(array_merge([$c->getId()], $this->getChoiceChildren($field, $c)));
            }
        }

        return null;
    }

    private function getChoiceChildren(CustomDefAbstract $field, CustomDefAbstract $node)
    {
        $ret = [];

        foreach ($field->getChildren() as $child) {
            if ($child->getOption('parent_id') == $node->getId()) {
                $ret = array_merge([$child->getId()], $this->getChoiceChildren($field, $child));
            }
        }

        return $ret;
    }

    /**
     * @param $str
     *
     * @return string
     */
    private function normalizeString($str)
    {
        $str = strtolower($str);
        $str = preg_replace('#\s+#', '', $str);

        return $str;
    }
}
