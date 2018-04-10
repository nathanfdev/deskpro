<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\EntityRepository\CustomDefAbstract as CustomDefAbstractRepos;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Terms;

class ChoiceFieldOptionMapper implements OptionMappterInterface
{
    /**
     * @var CustomDefAbstractRepos
     */
    private $ticketFieldRepos;

    /**
     * ChoiceFieldOptionMapper constructor.
     *
     * @param CustomDefAbstractRepos $ticketFieldRepos
     */
    public function __construct(CustomDefAbstractRepos $ticketFieldRepos)
    {
        $this->ticketFieldRepos = $ticketFieldRepos;
    }

    public function getValue($fieldId, $value)
    {
        $findTitle = $this->normalizeString($value);

        list($type, $customFieldId) = Terms::parseCustomFieldId($fieldId);

        switch ($type) {
            case 'ticket.data':
                /** @var CustomDefTicket $field */
                $field = $this->ticketFieldRepos->find($customFieldId);
                if (!$field) {
                    return null;
                }

                foreach ($field->getChildren() as $c) {
                    if ($findTitle === $this->normalizeString($c->getTitle())) {
                        return $c->getId();
                    }
                }

                break;

            default:
                return null;
        }
    }

    private function normalizeString($str)
    {
        $str = strtolower($str);
        $str = preg_replace('#\s+#', '', $str);

        return $str;
    }
}
