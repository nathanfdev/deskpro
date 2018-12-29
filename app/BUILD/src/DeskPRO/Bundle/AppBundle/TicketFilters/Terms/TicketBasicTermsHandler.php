<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\NewSearch\Manager\Elasticsearch;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\CheckValueUtils;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\ElasticQueryUtils;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\SqlQueryUtils;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\ValueFormatter;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\MemoizeMethod;
use Elastica\Query\BoolQuery;
use Elastica\Query\QueryString;
use Elastica\Util as ElasticaUtil;

/**
 * Class TicketBasicTermsHandler.
 */
class TicketBasicTermsHandler implements ValueTermHandlerInterface, SqlTermHandlerInterface, ElasticTermHandlerInterface
{
    use MemoizeMethod;

    /**
     * @var Elasticsearch
     */
    private $elasticSearch;

    /**
     * Constructor.
     *
     * @param Elasticsearch $elasticSearch
     */
    public function __construct(Elasticsearch $elasticSearch)
    {
        $this->elasticSearch = $elasticSearch;
    }

    /**
     * @return HandlerDef
     */
    public function getValueHandlerDef()
    {
        return $this->memoizedRun(function () {
            return HandlerDef::create()
                ->addField(TermFieldIds::TICKET_ID, Query::commonIdOperators())
                ->addField(TermFieldIds::ORG_ID, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_STATUS, Query::commonStringValueOperators())
                ->addField(TermFieldIds::TICKET_TICKET_STATUS_ID, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_DEPARTMENT, Query::commonIdOperators())
                ->addField(TermFieldIds::TICKET_AGENT, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_AGENT_TEAM, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_FOLLOWERS, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_LANGUAGE, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_PRODUCT, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_CATEGORY, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_PRIORITY, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_URGENCY, Query::commonValueOperators())
                ->addField(TermFieldIds::TICKET_WORKFLOW, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_LABELS, Query::commonStringValueOperators())
                ->addField(TermFieldIds::TICKET_EMAIL_ACCOUNT, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_IS_HOLD, Query::commonBoolValueOperators())
                ->addField(TermFieldIds::TICKET_PROBLEM_ID, Query::commonIdReferenceOperators());
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
            case TermFieldIds::TICKET_ID:
                $fieldValue = $ticketModel->id;
                break;
            case TermFieldIds::ORG_ID:
                $fieldValue = $ticketModel->organization->id;
                break;
            case TermFieldIds::TICKET_STATUS:
                $fieldValue = $ticketModel->status;
                break;
            case TermFieldIds::TICKET_DEPARTMENT:
                $fieldValue = $ticketModel->department;
                break;
            case TermFieldIds::TICKET_AGENT:
                $fieldValue = $ticketModel->agent;
                break;
            case TermFieldIds::TICKET_AGENT_TEAM:
                $fieldValue = $ticketModel->agent_team;
                break;
            case TermFieldIds::TICKET_FOLLOWERS:
                $fieldValue = $ticketModel->followers;
                break;
            case TermFieldIds::TICKET_LANGUAGE:
                $fieldValue = $ticketModel->language;
                break;
            case TermFieldIds::TICKET_PRODUCT:
                $fieldValue = $ticketModel->product;
                break;
            case TermFieldIds::TICKET_CATEGORY:
                $fieldValue = $ticketModel->category;
                break;
            case TermFieldIds::TICKET_PRIORITY:
                $fieldValue = $ticketModel->priority;
                break;
            case TermFieldIds::TICKET_URGENCY:
                $fieldValue = $ticketModel->urgency;
                break;
            case TermFieldIds::TICKET_WORKFLOW:
                $fieldValue = $ticketModel->workflow;
                break;
            case TermFieldIds::TICKET_LABELS:
                $fieldValue = $ticketModel->labels;
                break;
            case TermFieldIds::TICKET_EMAIL_ACCOUNT:
                $fieldValue = $ticketModel->email_account;
                break;
            case TermFieldIds::TICKET_IS_HOLD:
                $fieldValue = $ticketModel->is_hold;
                break;
            case TermFieldIds::TICKET_PROBLEM_ID:
                return false;
            default:
                throw new \InvalidArgumentException('Unknown field');
        }

        return CheckValueUtils::checkValue($fieldValue, $operator, $options);
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
    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::TICKET_ID:
                $column = '{tickets}.id';
                break;
            case TermFieldIds::ORG_ID:
                $column = '{tickets}.organization_id';
                break;
            case TermFieldIds::TICKET_STATUS:
                $column = '{tickets}.status';
                break;
            case TermFieldIds::TICKET_TICKET_STATUS_ID:
                $column = '{tickets}.ticket_status_id';
                break;
            case TermFieldIds::TICKET_DEPARTMENT:
                $column = '{tickets}.department_id';
                break;
            case TermFieldIds::TICKET_AGENT:
                $column = '{tickets}.agent_id';
                break;
            case TermFieldIds::TICKET_AGENT_TEAM:
                $column = '{tickets}.agent_team_id';
                break;
            case TermFieldIds::TICKET_LANGUAGE:
                $column = '{tickets}.language_id';
                break;
            case TermFieldIds::TICKET_PRODUCT:
                $column = '{tickets}.product_id';
                break;
            case TermFieldIds::TICKET_CATEGORY:
                $column = '{tickets}.category_id';
                break;
            case TermFieldIds::TICKET_PRIORITY:
                $column = '{tickets}.priority_id';
                break;
            case TermFieldIds::TICKET_URGENCY:
                $column = '{tickets}.urgency';
                break;
            case TermFieldIds::TICKET_WORKFLOW:
                $column = '{tickets}.workflow_id';
                break;
            case TermFieldIds::TICKET_EMAIL_ACCOUNT:
                $column = '{tickets}.email_account_id';
                break;
            case TermFieldIds::TICKET_IS_HOLD:
                $column = '{tickets}.status';
                break;
            default:
                $column = null;
        }

        if ($column) {
            return SqlQueryUtils::buildQueryCondition($column, $operator, $options);
        }

        switch ($fieldId) {
            case TermFieldIds::TICKET_FOLLOWERS:
                $cond = new SqlCondition();
                $cond->addUniqueJoin('tickets', 'tickets_participants', 'part', '{part}.ticket_id = {tickets}.id');

                return SqlQueryUtils::buildQueryCondition('{part}.person_id', $operator, $options, $cond);

            case TermFieldIds::TICKET_LABELS:
                $cond = new SqlCondition();
                $cond->addUniqueJoin('tickets', 'labels_tickets', 'label', '{label}.ticket_id = {tickets}.id');

                return SqlQueryUtils::buildQueryCondition('{label}.label', $operator, $options, $cond);

            case TermFieldIds::TICKET_PROBLEM_ID:
                $cond = new SqlCondition();
                $cond->addUniqueJoin('tickets', 'problem2tickets', 'prob', '{prob}.ticket_id = {tickets}.id');

                return SqlQueryUtils::buildQueryCondition('{prob}.problem_id', $operator, $options, $cond);
        }

        throw new \InvalidArgumentException('Unknown field');
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryFuncCondition($name, $fieldId, $operator, array $params, Context $context, Term $term)
    {
        throw new \RuntimeException('No functions defined');
    }

    /**
     * {@inheritdoc}
     */
    public function getElasticHandlerDef()
    {
        return $this->memoizedRun(function () {
            return HandlerDef::create()
                ->addField(TermFieldIds::TICKET_ID, Query::commonIdOperators())
                ->addField(TermFieldIds::ORG_ID, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_DEPARTMENT, Query::commonIdOperators())
                ->addField(TermFieldIds::TICKET_AGENT, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_AGENT_TEAM, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_FOLLOWERS, Query::commonIdReferenceOperators())
                ->addField(TermFieldIds::TICKET_LABELS, Query::commonStringValueOperators());
        }, __FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function buildElasticCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        $column = null;

        switch ($fieldId) {
            case TermFieldIds::TICKET_ID:
                $version = $this->elasticSearch->getVersion();
                if ($version && version_compare($version, '5.0.0') >= 0) {
                    $column = '_uid';
                } else {
                    $column = '_id';
                }

                break;
            case TermFieldIds::ORG_ID:
                $column = 'organization_id';
                break;
            case TermFieldIds::TICKET_DEPARTMENT:
                $column = 'department';
                break;
            case TermFieldIds::TICKET_AGENT:
                $column = 'agent';
                break;
            case TermFieldIds::TICKET_AGENT_TEAM:
                $column = 'agent_team';
                break;
            case TermFieldIds::TICKET_FOLLOWERS:
                $column = 'participants';
                break;
            case TermFieldIds::TICKET_LABELS:
                $labels = ValueFormatter::formatList(ValueFormatter::formatCheckValue($options));

                $term = new BoolQuery();
                foreach ($labels as $label) {
                    $queryString = new QueryString(ElasticaUtil::escapeTerm($label));
                    $queryString->setFields(['labels']);
                    $queryString->setDefaultOperator('AND');

                    $term->addShould($queryString);
                }

                return $term;
        }

        if ($column) {
            return ElasticQueryUtils::buildQuery($column, $operator, $options);
        }

        throw new \InvalidArgumentException("Unknown field $fieldId");
    }

    /**
     * {@inheritdoc}
     */
    public function buildElasticFuncCondition($name, $fieldId, $operator, array $params, Context $context, Term $term)
    {
        throw new \RuntimeException('No functions defined');
    }
}
