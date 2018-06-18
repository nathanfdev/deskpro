<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\Entity\TicketSla;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\CheckValueUtils;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\SqlQueryUtils;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\MemoizeMethod;

/**
 * Class TicketSlaTermsHandler.
 */
class TicketSlaTermsHandler implements ValueTermHandlerInterface, SqlTermHandlerInterface, ElasticTermHandlerInterface
{
    use MemoizeMethod;

    /**
     * @return HandlerDef
     */
    public function getValueHandlerDef()
    {
        return $this->memoizedRun(function () {
            return HandlerDef::create()
                ->addField(TermFieldIds::TICKET_SLAS, Query::commonIdReferenceOperators())
                ->addFunction('passingSlas', [Query::OP_HAS], [TermFieldIds::TICKET_SLAS])
                ->addFunction('warningSlas', [Query::OP_HAS], [TermFieldIds::TICKET_SLAS])
                ->addFunction('failedSlas', [Query::OP_HAS], [TermFieldIds::TICKET_SLAS]);
        }, __FUNCTION__);
    }

    /**
     * @return HandlerDef
     */
    public function getSqlHandlerDef()
    {
        return $this->getValueHandlerDef();
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

        return CheckValueUtils::checkValue($fieldValue, $operator, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatchFunc($name, $fieldId, $operator, array $params, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($name) {
            case 'passingSlas':
                return $this->matchAnySlasStatus($ticketModel, TicketSla::STATUS_OK, $params);
            case 'warningSlas':
                return $this->matchAnySlasStatus($ticketModel, TicketSla::STATUS_WARNING, $params);
            case 'failedSlas':
                return $this->matchAnySlasStatus($ticketModel, TicketSla::STATUS_FAIL, $params);
        }

        throw new \InvalidArgumentException();
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

                return SqlQueryUtils::buildQueryCondition('{slas}.sla_id', $operator, $options, $cond);
                break;
            default:
                throw new \InvalidArgumentException('Unknown field');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryFuncCondition($name, $fieldId, $operator, array $params, Context $context, Term $term)
    {
        switch (strtolower($name)) {
            case 'passingslas':
                return $this->buildHasAnySlas(TicketSla::STATUS_OK, $params);
            case 'warningslas':
                return $this->buildHasAnySlas(TicketSla::STATUS_WARNING, $params);
            case 'failedslas':
                return $this->buildHasAnySlas(TicketSla::STATUS_FAIL, $params);
        }

        throw new \InvalidArgumentException();
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

    /**
     * {@inheritdoc}
     */
    public function getElasticHandlerDef()
    {
        return $this->memoizedRun(function () {
            return HandlerDef::create();
        }, __FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function buildElasticCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        throw new \RuntimeException('No fields defined');
    }

    /**
     * {@inheritdoc}
     */
    public function buildElasticFuncCondition($name, $fieldId, $operator, array $params, Context $context, Term $term)
    {
        throw new \RuntimeException('No functions defined');
    }
}
