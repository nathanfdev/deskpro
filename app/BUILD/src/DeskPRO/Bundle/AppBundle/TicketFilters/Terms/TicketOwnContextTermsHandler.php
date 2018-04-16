<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use Doctrine\DBAL\Connection;

class TicketOwnContextTermsHandler extends AbstractTermsHandler
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getHandledFields()
    {
        return [
            Terms::TICKET_STARRED,
        ];
    }

    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($fieldId) {
            case Terms::TICKET_STARRED:
                if ($context->getAgentId() && $ticketModel->id) {
                    $flaggedWith = $this->db->fetchColumn('
                        SELECT color
                        FROM tickets_flagged
                        WHERE person_id = ? AND ticket_id = ?
                    ', [$context->getAgentId(), $ticketModel->id]) ?: null;
                } else {
                    $flaggedWith = null;
                }

                return $this->checkValue($flaggedWith, $operator, $options);

            default:
                throw new \InvalidArgumentException();
        }
    }

    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        switch ($fieldId) {
            case Terms::TICKET_STARRED:
                $cond = new SqlCondition();

                // no context, the filter makes no sense
                if (!$context->getAgentId()) {
                    $cond->setWhere('0');

                    return $cond;
                }

                $cond->addUniqueJoin('tickets', 'tickets_flagged', 'flag', '{flag}.ticket_id = {tickets}.id AND {flag}.person_id = :person_id')
                    ->setParam('person_id', $context->getAgentId());

                return $this->checkValueQueryCondition('{flag}.color', $operator, $options, $cond);

            default:
                throw new \InvalidArgumentException();
        }
    }
}
