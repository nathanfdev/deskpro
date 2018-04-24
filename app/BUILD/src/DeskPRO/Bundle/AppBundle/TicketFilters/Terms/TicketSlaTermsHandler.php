<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\Entity\TicketSla;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
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
            TermFieldIds::TICKET_SLAS,
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
                ->setFields(TermFieldIds::TICKET_SLAS)
                ->setMatchFn('matchHasPassingSlas')
                ->setQueryBuilderFn('buildHasPassingSlas')
                ->setOperators(Query::OP_HAS),

            FunctionCompareDef::create()
                ->setName('warningSlas')
                ->setFields(TermFieldIds::TICKET_SLAS)
                ->setMatchFn('matchHasWarningSlas')
                ->setQueryBuilderFn('buildHasWarningSlas')
                ->setOperators(Query::OP_HAS),

            FunctionCompareDef::create()
                ->setName('failedSlas')
                ->setFields(TermFieldIds::TICKET_SLAS)
                ->setMatchFn('matchHasFailingSlas')
                ->setQueryBuilderFn('buildHasFailingSlas')
                ->setOperators(Query::OP_HAS),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::TICKET_SLAS: $fieldValue = $ticketModel->id; break;
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

    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::TICKET_SLAS:
                $cond = new SqlCondition();
                $cond->addUniqueJoin('tickets', 'ticket_slas', 'slas', '{slas}.ticket_id = {tickets}.id');

                return $this->checkValueQueryCondition('{slas}.sla_id', $operator, $options, $cond);
                break;
            default:
                throw new \InvalidArgumentException('Unknown field');
        }
    }

    public function buildHasPassingSlas($fieldId, $operator, array $params, Context $context, Term $term)
    {
        return $this->buildHasAnySlas(TicketSla::STATUS_OK, $params);
    }

    public function buildHasWarningSlas($fieldId, $operator, array $params, Context $context, Term $term)
    {
        return $this->buildHasAnySlas(TicketSla::STATUS_WARNING, $params);
    }

    public function buildHasFailingSlas($fieldId, $operator, array $params, Context $context, Term $term)
    {
        return $this->buildHasAnySlas(TicketSla::STATUS_FAIL, $params);
    }
    public function buildHasAnySlas($findStatus, array $specificIds = null)
    {
        $cond = new SqlCondition();
        $cond->addUniqueJoin('tickets', 'ticket_slas', 'slas', '{slas}.ticket_id = {tickets}.id');
        if ($specificIds) {
            $cond->setWhere('{slas}.sla_id IN (:sla_ids) AND {slas}.sla_status = :sla_status')
                 ->setParam('sla_ids', $specificIds)
                 ->setParam('sla_status', $findStatus);
        } else {
            $cond->setWhere('{slas}.sla_status = :sla_status')
                 ->setParam('sla_status', $findStatus);
        }

        return $cond;
    }
}
