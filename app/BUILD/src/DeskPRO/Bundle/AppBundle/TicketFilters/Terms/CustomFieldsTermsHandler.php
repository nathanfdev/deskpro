<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Carbon\Carbon;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\CustomFieldSet;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomData;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomField;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptionMapperInterface;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\BetweenValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\CompareValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\CheckValueUtils;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\SqlQueryUtils;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MemoizeMethod;
use Orb\Util\Dates;

/**
 * Class CustomFieldsTermsHandler.
 */
class CustomFieldsTermsHandler implements ValueTermHandlerInterface, SqlTermHandlerInterface, ElasticTermHandlerInterface
{
    use MemoizeMethod;

    /**
     * @var CustomFieldSet
     */
    private $customFieldSet;

    /**
     * @var OptionMapperInterface
     */
    private $optionMapper;

    /**
     * Terms constructor.
     *
     * @param OptionMapperInterface $optionMappter
     */
    public function __construct(CustomFieldSet $customFieldSet, OptionMapperInterface $optionMappter = null)
    {
        $this->customFieldSet = $customFieldSet;
        $this->optionMapper   = $optionMappter;
    }

    /**
     * @return HandlerDef
     */
    public function getValueHandlerDef()
    {
        return $this->memoizedRun(function () {
            $def = HandlerDef::create();

            $fieldIds = array_merge(
                ListUtils::flatMap($this->customFieldSet->customTicketFields, function (CustomField $f) {
                    return ListUtils::map(array_merge([$f->field], $f->aliases), function ($x) {
                        return TermFieldIds::getCustomFieldTermId(TermFieldIds::TICKET_CUSTOM, $x);
                    });
                }),
                ListUtils::flatMap($this->customFieldSet->customPersonFields, function (CustomField $f) {
                    return ListUtils::map(array_merge([$f->field], $f->aliases), function ($x) {
                        return TermFieldIds::getCustomFieldTermId(TermFieldIds::PERSON_CUSTOM, $x);
                    });
                }),
                ListUtils::flatMap($this->customFieldSet->customOrgFields, function (CustomField $f) {
                    return ListUtils::map(array_merge([$f->field], $f->aliases), function ($x) {
                        return TermFieldIds::getCustomFieldTermId(TermFieldIds::ORG_CUSTOM, $x);
                    });
                })
            );

            foreach ($fieldIds as $fieldId) {
                $def->addField($fieldId, '*');
            }

            $def->addFunction('Option', [Query::OP_HAS, Query::OP_IN, Query::OP_NOT_IN, Query::OP_EQ, Query::OP_NEQ], $fieldIds);

            return $def;
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
        list($realFieldId, $field, $type) = $this->customFieldSet->getFieldInfoById($fieldId);

        switch ($type) {
            case TermFieldIds::TICKET_CUSTOM:
                $dataCollection = $ticketModel->custom_fields;
                break;
            case TermFieldIds::PERSON_CUSTOM:
                $dataCollection = $ticketModel->person->custom_fields;
                break;
            case TermFieldIds::ORG_CUSTOM:
                $dataCollection = $ticketModel->person->custom_fields;
                break;
            default:
                throw new \InvalidArgumentException();
        }

        /** @var CustomData[] $fieldDataRecs */
        $fieldDataRecs = ListUtils::filter($dataCollection, function (CustomData $d) use ($field) {
            return $d->field == $field->field;
        });

        /**
         * @fixme: If custom field is "agent only" it does not show up
         * in $dataCollection, thus fails to validate further
         */
        if (empty($fieldDataRecs)) {
            return $this->checkCustomDataValue($field, new CustomData($field->field, null), $operator, $options);
        }

        // at least one match
        return ListUtils::first($fieldDataRecs, function (CustomData $d) use ($field, $operator, $options) {
            return $this->checkCustomDataValue($field, $d, $operator, $options);
        }) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatchFunc($name, $fieldId, $operator, array $params, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($name) {
            case 'Option':
                return $this->doesTicketMatchFieldOption($fieldId, $operator, $params, $ticketModel, $context, $term);
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param string      $fieldId
     * @param string      $operator
     * @param array       $params
     * @param TicketModel $ticketModel
     * @param Context     $context
     * @param Term        $term
     *
     * @throws \Exception
     *
     * @return bool
     */
    private function doesTicketMatchFieldOption($fieldId, $operator, array $params, TicketModel $ticketModel, Context $context, Term $term)
    {
        if (!$this->optionMapper) {
            throw new \RuntimeException('No option mapper is registered');
        }

        $newOptions = $this->getOptionIdFromTitleValue($fieldId, $params[0]);

        return $this->doesTicketMatch($fieldId, $operator, $newOptions, $ticketModel, $context, $term);
    }

    /**
     * Pre-process field and check value before passing to our generic checkValue.
     * e.g. dates are compared as dates.
     *
     * @param CustomField $field
     * @param CustomData  $data
     * @param string      $operator
     * @param OptValue    $optValue
     *
     * @return bool
     */
    protected function checkCustomDataValue(CustomField $field, CustomData $data, $operator, OptValue $optValue)
    {
        $fieldValue = $data->value;
        $checkValue = $optValue;

        switch ($field->type) {
            case CustomDefAbstract::TYPE_DATE:
            case CustomDefAbstract::TYPE_DATETIME:
                $dateConvert = function($date) {

                    if (! is_null($date)) {
                        if (is_string($date)) {
                            $date = Carbon::parse($date);
                        } elseif (is_int($date)) {
                            $date = Carbon::parse("@$date");
                        } elseif (is_array($date)) {
                            $size = count($date);
                            $date = array_filter(array_map(function ($value) {
                                $value = (is_string($value))
                                    ? Carbon::parse($value)
                                    : (is_int($value))
                                        ? Carbon::parse("@$value")
                                        : $value;

                                return ($value instanceof \DateTime)
                                    ? $value->setTimezone(new \DateTimeZone('UTC'))
                                    : null;
                            }, $date));

                            $date = ($size === count($date)) ? $date : null;
                        } else {
                            $date = null;
                        }
                    }

                    return ($date instanceof \DateTime)
                        ? $date->setTimezone(new \DateTimeZone('UTC'))
                        : $date;
                };

                $fieldValue = $dateConvert($fieldValue);
                $checkValue = $dateConvert($checkValue->getValue());

                if (is_null($fieldValue) || is_null($checkValue)) {
                    return false;
                }

                break;

            case CustomDefAbstract::TYPE_CHOICE:
                // choice fields are always collections, so we need
                // to rewrite = and != into IN and NOT IN
                if ($operator === Query::OP_EQ) {
                    $operator = Query::OP_IN;
                } elseif ($operator === Query::OP_NEQ) {
                    $operator = Query::OP_NOT_IN;
                }
                break;

            case CustomDefAbstract::TYPE_TOGGLE:
                $fieldValue = $data->value;
                $checkValue = $optValue;

                if ($fieldValue === null) {
                    $fieldValue = 0;
                }
                break;

            default:
                $fieldValue = $data->value;
                $checkValue = $optValue;
                break;
        }

        return CheckValueUtils::checkValue($fieldValue, $operator, $checkValue);
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        /** @var $field CustomField */
        list($realFieldId, $field, $type) = $this->customFieldSet->getFieldInfoById($fieldId);

        $cond = new SqlCondition();

        switch ($type) {
            case TermFieldIds::TICKET_CUSTOM:
                $cond->addUniqueJoin('tickets', 'custom_data_ticket', 'dat', '{dat}.ticket_id = {tickets}.id AND {dat}.root_field_id = :rootFieldId');
                break;
            case TermFieldIds::PERSON_CUSTOM:
                $cond->addUniqueJoin('tickets', 'custom_data_person', 'dat', '{dat}.person_id = {tickets}.person_id AND {dat}.root_field_id = :rootFieldId');
                break;
            case TermFieldIds::ORG_CUSTOM:
                $cond->addUniqueJoin('tickets', 'custom_data_organizations', 'dat', '{dat}.organization_id = {tickets}.organization_id AND {dat}.root_field_id = :rootFieldId');
                break;
            default:
                throw new \RuntimeException();
        }

        $cond->setParam('rootFieldId', $field->field);

        switch ($field->type) {
            case CustomDefAbstract::TYPE_CHOICE:
                return SqlQueryUtils::buildQueryCondition('{dat}.field_id', $operator, $options, $cond);

            case CustomDefAbstract::TYPE_TOGGLE:
                return SqlQueryUtils::buildQueryCondition('{dat}.value', $operator, $options, $cond);

            case CustomDefAbstract::TYPE_DATETIME:
            case CustomDefAbstract::TYPE_DATE:

                $inputToTs = function ($checkValue) {
                    if (!$checkValue instanceof \DateTime) {
                        try {
                            $checkValue = new \DateTime($checkValue);
                        } catch (\Exception $e) {
                            $checkValue = null;
                        }
                        if (!$checkValue) {
                            return SqlCondition::create()->setWhere('0');
                        }
                    }

                    if ($checkValue instanceof \DateTime) {
                        $tmp = clone $checkValue;
                        $tmp->setTimezone(new \DateTimeZone('UTC'));
                        $checkValue = $tmp->getTimestamp();
                    }

                    return $checkValue;
                };

                if ($options instanceof BetweenValue) {
                    $values = ListUtils::map($options->getValue(), $inputToTs);

                    return SqlQueryUtils::buildQueryCondition('{dat}.value', Query::OP_BETWEEN, $values, $cond);
                } else {
                    $checkValue = $inputToTs($options->getValue());

                    return SqlQueryUtils::buildQueryCondition('{dat}.value', $operator, $checkValue, $cond);
                }

            case CustomDefAbstract::TYPE_CURRENCY:
                return SqlQueryUtils::buildQueryCondition('{dat}.value', $operator, $options, $cond);

            default:
                return SqlQueryUtils::buildQueryCondition('{dat}.input', $operator, $options, $cond);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryFuncCondition($name, $fieldId, $operator, array $params, Context $context, Term $term)
    {
        switch (strtolower($name)) {
            case 'option':
                return $this->buildFieldOptionQueryCondition($fieldId, $operator, $params, $context, $term);
        }

        throw new \InvalidArgumentException();
    }

    private function buildFieldOptionQueryCondition($fieldId, $operator, array $params, Context $context, Term $term)
    {
        if (!$this->optionMapper) {
            throw new \RuntimeException('No option mapper is registered');
        }

        $newOptions = $this->getOptionIdFromTitleValue($fieldId, $params[0]);

        return $this->buildQueryCondition($fieldId, $operator, $newOptions, $context, $term);
    }

    private function getOptionIdFromTitleValue($fieldId, $v)
    {
        /** @var $field CustomField */
        list($realFieldId, $field, $type) = $this->customFieldSet->getFieldInfoById($fieldId);

        $value = $this->optionMapper->getValue($type.'.'.$field->field, $v) ?: $v;

        return new CompareValue($value);
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
