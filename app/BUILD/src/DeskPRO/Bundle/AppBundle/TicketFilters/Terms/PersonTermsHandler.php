<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\EntityRepository\Person as PersonRepos;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\CheckValueUtils;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\ElasticQueryUtils;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\SqlQueryUtils;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MemoizeMethod;

/**
 * Class PersonTermsHandler.
 */
class PersonTermsHandler implements ValueTermHandlerInterface, SqlTermHandlerInterface, ElasticTermHandlerInterface
{
    use MemoizeMethod;

    /**
     * @var PersonRepos
     */
    private $personRepos;

    /**
     * PersonTermsHandler constructor.
     *
     * @param PersonRepos $personRepos
     */
    public function __construct(PersonRepos $personRepos)
    {
        $this->personRepos = $personRepos;
    }

    /**
     * @return HandlerDef
     */
    public function getValueHandlerDef()
    {
        return $this->memoizedRun(function () {
            return HandlerDef::create()
                ->addField(TermFieldIds::PERSON_ID, Query::commonIdOperators());
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
            case TermFieldIds::PERSON_ID:
                $value = $this->normalizePersonId($options->getValue());

                return CheckValueUtils::checkValue($ticketModel->person->id, $operator, $value);

            default:
                throw new \InvalidArgumentException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::PERSON_ID:
                $value = $this->normalizePersonId($options->getValue());

                return SqlQueryUtils::buildQueryCondition('{tickets}.person_id', $operator, $value);

            default:
                throw new \InvalidArgumentException();
        }
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
    public function doesTicketMatchFunc($name, $fieldId, $operator, array $params, TicketModel $ticketModel, Context $context, Term $term)
    {
        throw new \RuntimeException('No functions defined');
    }

    /**
     * {@inheritdoc}
     */
    public function getElasticHandlerDef()
    {
        return $this->getValueHandlerDef();
    }

    /**
     * {@inheritdoc}
     */
    public function buildElasticCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::PERSON_ID:
                $value = $this->normalizePersonId($options->getValue());

                return ElasticQueryUtils::buildQuery('person_id', $operator, $value);

            default:
                throw new \InvalidArgumentException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildElasticFuncCondition($name, $fieldId, $operator, array $params, Context $context, Term $term)
    {
        throw new \RuntimeException('No functions defined');
    }

    /**
     * @param $value
     *
     * @return array|int
     */
    private function normalizePersonId($value)
    {
        if (is_array($value)) {
            return ListUtils::map($value, function ($v) {
                return $this->normalizePersonId($v);
            });
        }

        if (is_numeric($value) || ctype_digit($value)) {
            return (int) $value;
        } elseif (is_string($value) && strpos($value, '@') !== false) {
            $person = $this->personRepos->findOneByEmail($value);
            if ($person) {
                return $person->getId();
            }
        }

        return 0;
    }
}
