<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\Entity\TicketFlagged;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\CheckValueUtils;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\SqlQueryUtils;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\Util\ListUtils;
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
            TermFieldIds::TICKET_STARRED,
        ];
    }

    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::TICKET_STARRED:
                $value = $this->mapIdToColor($options->getValue());
                if ($context->getAgentId() && $ticketModel->id) {
                    $flaggedWith = $this->db->fetchColumn('
                        SELECT color
                        FROM tickets_flagged
                        WHERE person_id = ? AND ticket_id = ?
                    ', [$context->getAgentId(), $ticketModel->id]) ?: null;
                } else {
                    $flaggedWith = null;
                }

                return CheckValueUtils::checkValue($flaggedWith, $operator, $value);

            default:
                throw new \InvalidArgumentException();
        }
    }

    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::TICKET_STARRED:
                $cond = new SqlCondition();

                // no context, the filter makes no sense
                if (!$context->getAgentId()) {
                    $cond->setWhere('0');

                    return $cond;
                }

                $cond->addUniqueJoin('tickets', 'tickets_flagged', 'flag', '{flag}.ticket_id = {tickets}.id AND {flag}.person_id = :person_id')
                    ->setParam('person_id', $context->getAgentId());

                $value = $this->mapIdToColor($options->getValue());

                return SqlQueryUtils::buildQueryCondition('{flag}.color', $operator, $value, $cond);

            default:
                throw new \InvalidArgumentException();
        }
    }

    /**
     * Maps an int "id" to the color that is stored in the db. apiv2 uses "ids" for stars
     * because we want to make them customisable per-agent at some point.
     *
     * @param string $id
     *
     * @return array
     */
    private function mapIdToColor($id)
    {
        if (is_array($id)) {
            return ListUtils::map($id, function ($x) {
                return $this->mapIdToColor($x);
            });
        }

        return isset(TicketFlagged::$colorMap[$id]) ? TicketFlagged::$colorMap[$id] : $id;
    }
}
