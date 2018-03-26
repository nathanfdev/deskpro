<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\Entity\TicketSla;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class TicketSlaTermsHandler extends AbstractTermsHandler
{
    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return [
            Terms::TICKET_SLAS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCompareFunctions()
    {
        return [
            FunctionCompareDef::create()
                ->setName('passingSlas')
                ->setFields(Terms::TICKET_SLAS)
                ->setMatchFn('matchHasPassingSlas')
                ->setOperators(Query::OP_HAS),

            FunctionCompareDef::create()
                ->setName('warningSlas')
                ->setFields(Terms::TICKET_SLAS)
                ->setMatchFn('matchHasWarningSlas')
                ->setOperators(Query::OP_HAS),

            FunctionCompareDef::create()
                ->setName('failedSlas')
                ->setFields(Terms::TICKET_SLAS)
                ->setMatchFn('matchHasFailingSlas')
                ->setOperators(Query::OP_HAS),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($fieldId) {
            case Terms::TICKET_SLAS: $fieldValue = $ticketModel->id; break;
            default: throw new \InvalidArgumentException('Unknown field');
        }

        return $this->checkValue($fieldValue, $operator, $options);
    }

    public function matchHasPassingSlas($fieldId, $operator, array $params, TicketModel $ticketModel, Context $context, Term $term)
    {
        return $this->matchAnySlasStatus($ticketModel, TicketSla::STATUS_OK, $params);
    }

    public function matchHasWarningSlas($fieldId, $operator, array $params, TicketModel $ticketModel, Context $context, Term $term)
    {
        return $this->matchAnySlasStatus($ticketModel, TicketSla::STATUS_WARNING, $params);
    }

    public function matchHasFailingSlas($fieldId, $operator, array $params, TicketModel $ticketModel, Context $context, Term $term)
    {
        return $this->matchAnySlasStatus($ticketModel, TicketSla::STATUS_FAIL, $params);
    }

    /**
     * @param TicketModel $ticketModel
     * @param string      $findStatus
     * @param array       $specificIds
     *
     * @return bool
     */
    private function matchAnySlasStatus(TicketModel $ticketModel, $findStatus, array $specificIds)
    {
        foreach ($ticketModel->slasInfo as $slaInfo) {
            if ($slaInfo->status === $findStatus) {
                if (empty($specificIds) || in_array($slaInfo->sla_id, $specificIds)) {
                    return true;
                }
            }
        }

        return false;
    }
}
