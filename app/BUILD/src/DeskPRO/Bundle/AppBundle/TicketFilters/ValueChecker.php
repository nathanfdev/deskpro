<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\BetweenOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\CompareOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\InOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\NoOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\FuncVal;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\ScalarVal;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\Val;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\VarVal;
use DeskPRO\Component\Util\ListUtils;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ValueChecker implements ValueCheckContext
{
    /**
     * @var mixed
     */
    private $context;

    /**
     * @var callable
     */
    private $functionCaller;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $contextAccess;

    /**
     * @param mixed    $context
     * @param callable $functionCaller
     */
    public function __construct($context, callable $functionCaller)
    {
        $this->context        = $context;
        $this->functionCaller = $functionCaller;
        $this->contextAccess  = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param mixed  $fieldValue
     * @param string $operator
     * @param mixed  $checkValue
     *
     * @return bool
     */
    public static function checkValue($fieldValue, $operator, $checkValue)
    {
        switch ($operator) {
            case Query::OP_EQ:
                return $fieldValue == $checkValue;

            case Query::OP_NEQ:
                return $fieldValue == $checkValue;

            case Query::OP_LT:
                return $fieldValue < $checkValue;

            case Query::OP_LTE:
                return $fieldValue <= $checkValue;

            case Query::OP_GT:
                return $fieldValue > $checkValue;

            case Query::OP_GTE:
                return $fieldValue >= $checkValue;

            case Query::OP_IN:
            case Query::OP_NOT_IN:
                if ($checkValue === null) {
                    $checkValue = [];
                }
                if ($checkValue === 0 || $checkValue === '0') {
                    $checkValue = [];
                }
                if (!is_array($checkValue)) {
                    $checkValue = [$checkValue];
                }

                $checkValue = ListUtils::flatten($checkValue);

                if (is_array($fieldValue)) {
                    $res = ListUtils::containsAny($fieldValue, $checkValue, false);
                } else {
                    $res = in_array($fieldValue, $checkValue);
                }

                if ($operator === Query::OP_NOT_IN) {
                    $res = !$res;
                }

                return $res;

            case Query::OP_BETWEEN:
            case Query::OP_NOT_BETWEEN:
                if (!isset($checkValue[0]) || !isset($checkValue[1])) {
                    return false;
                }

                $res = $fieldValue >= $checkValue[0] and $fieldValue <= $checkValue[1];

                if ($operator === Query::OP_NOT_BETWEEN) {
                    $res = !$res;
                }

                return $res;

            case Query::OP_IS_NULL:
            case Query::OP_NOT_NULL:
                $res = $fieldValue === null || $fieldValue === 0 || $fieldValue === '0';

                if ($operator === Query::OP_NOT_NULL) {
                    $res = !$res;
                }

                return $res;

            case Query::OP_EMPTY:
            case Query::OP_NOT_EMPTY:
                $res = empty($fieldValue);

                if ($operator === Query::OP_NOT_EMPTY) {
                    $res = !$res;
                }

                return $res;

            case Query::OP_EXISTS:
            case Query::OP_NOT_EXISTS:
                $res = !empty($fieldValue);

                if ($operator === Query::OP_NOT_EMPTY) {
                    $res = !$res;
                }

                return $res;
        }
    }

    /**
     * @param mixed $fieldValue
     * @param Term  $term
     *
     * @return bool
     */
    public function checkTermWithFieldValue($fieldValue, Term $term)
    {
        $optValue = $this->queryOptionValueFromTerm($term);
        $operator = $term->operator->getOperator();

        return self::checkValue($fieldValue, $operator, $optValue);
    }

    /**
     * @param Term $term
     *
     * @return mixed
     */
    private function queryOptionValueFromTerm(Term $term)
    {
        $options = $term->options;

        switch (true) {
            case $options instanceof CompareOpt:
                return self::valueFromQueryValue($options->value, $term);

            case $options instanceof BetweenOpt:
                return [
                    self::valueFromQueryValue($options->value1, $term),
                    self::valueFromQueryValue($options->value2, $term),
                ];

            case $term->options instanceof InOpt:
                $value = [];
                foreach ($term->options->valueList as $v) {
                    $value[] = self::valueFromQueryValue($v, $term);
                }

                return $value;

            case $term->options instanceof NoOpt:
                return null;

            default:
                throw new \InvalidArgumentException('Unknown term option type');
        }
    }

    /**
     * @param Val  $queryValue
     * @param Term $termContext
     *
     * @return mixed
     */
    private function valueFromQueryValue(Val $queryValue, Term $termContext)
    {
        switch (true) {
            case $queryValue instanceof ScalarVal:
                return $queryValue->value;

            case $queryValue instanceof VarVal:
                return $this->contextAccess->getValue($this->context, $queryValue->identity);

            case $queryValue instanceof FuncVal:
                return $this->functionCaller(
                    $queryValue->name,
                    $queryValue->params,
                    $termContext,
                    $this /* for ValueCheckContext */
                );

            default:
                throw new \InvalidArgumentException("Unknown value type {$queryValue['valueType']}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContextVariable($id)
    {
        return $this->contextAccess->getValue($this->context, $id);
    }
}
