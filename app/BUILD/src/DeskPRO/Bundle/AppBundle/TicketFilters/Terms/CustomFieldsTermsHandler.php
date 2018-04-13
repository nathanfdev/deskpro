<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomData;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomField;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptionMappterInterface;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\CompareValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\StringUtils;

class CustomFieldsTermsHandler extends AbstractTermsHandler
{
    /**
     * @var CustomField[]
     */
    private $customTicketFields = [];

    /**
     * @var CustomField[]
     */
    private $customPersonFields = [];

    /**
     * @var OptionMappterInterface
     */
    private $optionMapper;

    /**
     * Terms constructor.
     *
     * @param CustomField[]          $customTicketFields
     * @param CustomField[]          $customPersonFields
     * @param OptionMappterInterface $optionMappter
     */
    public function __construct(array $customTicketFields = [], array $customPersonFields = [], OptionMappterInterface $optionMappter = null)
    {
        $this->customTicketFields = $customTicketFields;
        $this->customPersonFields = $customPersonFields;
        $this->optionMapper       = $optionMappter;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompareFunctions()
    {
        return [
            FunctionCompareDef::create()
                ->setName('Option')
                ->setFields($this->getHandledFields())
                ->setMatchFn('doesTicketMatchFieldOption')
                ->setQueryBuilderFn('buildFieldOptionQueryCondition')
                ->setOperators(Query::OP_HAS, Query::OP_IN, Query::OP_NOT_IN, Query::OP_EQ, Query::OP_NEQ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        list($field, $type) = $this->getFieldPropsFromReference($fieldId);

        if ($type === 'ticket.data') {
            $dataCollection = $ticketModel->custom_fields;
        } else {
            $dataCollection = $ticketModel->person->custom_fields;
        }

        /** @var CustomData[] $fieldDataRecs */
        $fieldDataRecs = ListUtils::filter($dataCollection, function (CustomData $d) use ($field) {
            return $d->field == $field->field;
        });

        if (empty($fieldDataRecs)) {
            return $this->checkCustomDataValue($field, new CustomData($field->field, null), $operator, $options);
        }

        // at least one match
        return ListUtils::first($fieldDataRecs, function (CustomData $d) use ($field, $operator, $options) {
            return $this->checkCustomDataValue($field, $d, $operator, $options);
        }) !== null;
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
                try {
                    $fieldValue = new \DateTime('@'.$data->value);
                } catch (\Exception $e) {
                    $fieldValue = null;
                }
                if (!$fieldValue) {
                    return SqlCondition::create()->setWhere('0');
                }

                $checkValue = $optValue->getValue();
                if (!$checkValue instanceof \DateTime) {
                    $checkValue = @new \DateTime($checkValue);
                    if (!$checkValue) {
                        return SqlCondition::create()->setWhere('0');
                    }
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

        return $this->checkValue($fieldValue, $operator, $checkValue);
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        /** @var $field CustomField */
        list($field, $type) = $this->getFieldPropsFromReference($fieldId);

        if ($type === 'ticket.data') {
            $tableName = 'custom_data_ticket';
        } else {
            $tableName = 'custom_data_person';
        }

        $cond = new SqlCondition();
        $cond->addUniqueJoin('tickets', $tableName, 'dat', '{dat}.ticket_id = {tickets}.id AND {dat}.root_field_id = :rootFieldId');
        $cond->setParam('rootFieldId', $field->field);

        switch ($field->type) {
            case CustomDefAbstract::TYPE_CHOICE:
                return $this->checkValueQueryCondition('{dat}.field_id', $operator, $options, $cond);

            case CustomDefAbstract::TYPE_TOGGLE:
                $checkValue = $options->getValue();

                if (($operator === Query::OP_EQ && $checkValue == '0') || ($operator === Query::OP_NEQ && $checkValue == '1')) {
                    $cond->setWhere('{dat}.value = 0 OR {dat}.value IS NULL');

                    return $cond;
                }

                return $this->checkValueQueryCondition('{dat}.value', $operator, $options, $cond);

            case CustomDefAbstract::TYPE_DATETIME:
            case CustomDefAbstract::TYPE_DATE:
                $checkValue = $options->getValue();
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

                return $this->checkValueQueryCondition('{dat}.value', $operator, $checkValue, $cond);

            case CustomDefAbstract::TYPE_CURRENCY:
                return $this->checkValueQueryCondition('{dat}.value', $operator, $options, $cond);

            default:
                return $this->checkValueQueryCondition('{dat}.input', $operator, $options, $cond);
        }
    }

    public function doesTicketMatchFieldOption($fieldId, $operator, array $params, TicketModel $ticketModel, Context $context, Term $term)
    {
        if (!$this->optionMapper) {
            throw new \RuntimeException('No option mapper is registered');
        }

        $newOptions = $this->getOptionIdFromTitleValue($fieldId, $params[0]);

        return $this->doesTicketMatch($fieldId, $operator, $newOptions, $ticketModel, $context, $term);
    }

    public function buildFieldOptionQueryCondition($fieldId, $operator, array $params, Context $context, Term $term)
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
        list($field, $type) = $this->getFieldPropsFromReference($fieldId);

        $value = $this->optionMapper->getValue($type.'.'.$field->field, $v) ?: $v;

        return new CompareValue($value);
    }

    /**
     * @param string $fieldIdRef e.g. ticket.data.some_field
     *
     * @return array
     */
    private function getFieldPropsFromReference($fieldIdRef)
    {
        if ($fieldAlias = StringUtils::removeFromStart(sprintf(Terms::TICKET_CUSTOM, ''), $fieldIdRef)) {
            $fieldCollection = $this->customTicketFields;
            $type            = 'ticket.data';
        } elseif ($fieldAlias = StringUtils::removeFromStart(sprintf(Terms::PERSON_CUSTOM, ''), $fieldIdRef)) {
            $fieldCollection = $this->customPersonFields;
            $type            = 'ticket.person.data';
        } else {
            throw new \InvalidArgumentException('Unknown field type');
        }

        /** @var CustomField $field */
        $field = ListUtils::first($fieldCollection, function (CustomField $f) use ($fieldAlias) {
            return $f->field == $fieldAlias || in_array($fieldAlias, $f->aliases);
        });

        if (!$field) {
            throw new \InvalidArgumentException('Unknown field');
        }

        return [
            $field,
            $type,
            $fieldCollection,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return array_merge(
            ListUtils::flatMap($this->customTicketFields, function (CustomField $f) {
                return ListUtils::map(array_merge([$f->field], $f->aliases), function ($x) {
                    return sprintf(Terms::TICKET_CUSTOM, $x);
                });
            }),
            ListUtils::flatMap($this->customPersonFields, function (CustomField $f) {
                return ListUtils::map(array_merge([$f->field], $f->aliases), function ($x) {
                    return sprintf(Terms::PERSON_CUSTOM, $x);
                });
            })
        );
    }
}
