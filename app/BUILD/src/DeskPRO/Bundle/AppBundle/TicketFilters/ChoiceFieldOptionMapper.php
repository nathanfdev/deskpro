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
