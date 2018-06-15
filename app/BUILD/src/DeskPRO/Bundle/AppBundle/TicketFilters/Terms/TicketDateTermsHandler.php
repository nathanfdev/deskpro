<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\CheckValueUtils;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\SqlQueryUtils;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\MemoizeMethod;

class TicketDateTermsHandler implements ValueTermHandler, SqlTermHandler
{
    use MemoizeMethod;

    /**
     * @return HandlerDef
     */
    public function getValueHandlerDef()
    {
        return $this->memoizedRun(function () {
            return HandlerDef::create()
                ->addField(TermFieldIds::TICKET_DATE_CREATED, Query::commonDateValueOperators())
                ->addField(TermFieldIds::TICKET_DATE_RESOLVED, Query::commonDateValueOperators())
                ->addField(TermFieldIds::TICKET_DATE_LAST_AGENT_REPLY, Query::commonDateValueOperators())
                ->addField(TermFieldIds::TICKET_DATE_LAST_USER_REPLY, Query::commonDateValueOperators())
                ->addField(TermFieldIds::TICKET_DATE_AGENT_WAITING, Query::commonDateValueOperators())
                ->addField(TermFieldIds::TICKET_DATE_USER_WAITING, Query::commonDateValueOperators());
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
            case TermFieldIds::TICKET_DATE_CREATED:
                $fieldValue = $ticketModel->date_created;
                break;
            case TermFieldIds::TICKET_DATE_LAST_AGENT_REPLY:
                $fieldValue = $ticketModel->date_last_agent_reply;
                break;
            case TermFieldIds::TICKET_DATE_LAST_USER_REPLY:
                $fieldValue = $ticketModel->date_last_user_reply;
                break;
            case TermFieldIds::TICKET_DATE_AGENT_WAITING:
                $fieldValue = $ticketModel->date_agent_waiting;
                break;
            case TermFieldIds::TICKET_DATE_USER_WAITING:
                $fieldValue = $ticketModel->date_user_waiting;
                break;
            default:
                throw new \InvalidArgumentException();
        }

        return CheckValueUtils::checkValue($fieldValue, $operator, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        $column = null;
        switch ($fieldId) {
            case TermFieldIds::TICKET_DATE_CREATED:
                $column = '{tickets}.date_created';
                break;
            case TermFieldIds::TICKET_DATE_RESOLVED:
                $column = '{tickets}.date_resolved';
                break;
            case TermFieldIds::TICKET_DATE_LAST_USER_REPLY:
                $column = '{tickets}.date_last_user_reply';
                break;
            case TermFieldIds::TICKET_DATE_LAST_AGENT_REPLY:
                $column = '{tickets}.date_last_agent_reply';
                break;
            case TermFieldIds::TICKET_DATE_AGENT_WAITING:
                $column = '{tickets}.date_agent_waiting';
                break;
            case TermFieldIds::TICKET_DATE_USER_WAITING:
                $column = '{tickets}.date_user_waiting';
                break;
        }

        if ($column) {
            return SqlQueryUtils::buildQueryCondition($column, $operator, $options);
        }

        throw new \InvalidArgumentException('Unknown field');
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatchFunc($name, $fieldId, $operator, array $params, TicketModel $ticketModel, Context $context, Term $term)
    {
        throw new \RuntimeException('No functions defined');
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryFuncCondition($name, $fieldId, $operator, array $params, Context $context, Term $term)
    {
        throw new \RuntimeException('No functions defined');
    }
}
