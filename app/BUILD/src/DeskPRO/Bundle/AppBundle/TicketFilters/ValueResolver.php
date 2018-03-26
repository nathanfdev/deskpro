<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\BetweenValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\CompareValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\InValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\NoValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\BetweenOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\CompareOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\InOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\NoOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\FuncVal;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\RelativeTimeVal;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\ScalarVal;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\Val;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\VarVal;

class ValueResolver
{
    /**
     * @param FuncVal $funcVal
     * @param Term    $term
     * @param Context $context
     *
     * @return array
     */
    public function getFuncCallParamValues(FuncVal $funcVal, Term $term, Context $context)
    {
        $params = [];
        foreach ($funcVal->params as $p) {
            $params[] = $this->rawValueFromQueryVal($p, $term, $context);
        }

        return $params;
    }

    /**
     * @param Term    $term
     * @param Context $context
     *
     * @return OptValue
     */
    public function optionValueFromTerm(Term $term, Context $context)
    {
        $options = $term->options;

        switch (true) {
            case $options instanceof CompareOpt:
                return new CompareValue(self::rawValueFromQueryVal($options->value, $term, $context));

            case $options instanceof BetweenOpt:
                return new BetweenValue(
                    self::rawValueFromQueryVal($options->value1, $term, $context),
                    self::rawValueFromQueryVal($options->value2, $term, $context)
                );

            case $term->options instanceof InOpt:
                $values = [];
                foreach ($term->options->valueList as $v) {
                    $values[] = self::rawValueFromQueryVal($v, $term, $context);
                }

                return new InValue($values);

            case $term->options instanceof NoOpt:
                return new NoValue();

            default:
                throw new \InvalidArgumentException('Unknown term option type');
        }
    }

    /**
     * @param Val     $queryValue
     * @param Term    $termContext
     * @param Context $context
     *
     * @return mixed
     */
    private function rawValueFromQueryVal(Val $queryValue, Term $term, Context $context)
    {
        switch (true) {
            case $queryValue instanceof ScalarVal:
                return $queryValue->value;

            case $queryValue instanceof VarVal:
                return $context->getContextVariable($queryValue->identity);

            case $queryValue instanceof RelativeTimeVal:
                $time = new \DateTime();
                $sign = $queryValue->mode === RelativeTimeVal::MODE_FUTURE ? '+' : '-';

                foreach ($queryValue->times as $t) {
                    $num = (int) $t[0];
                    if (!$num) {
                        continue;
                    }
                    switch ($t[1]) {
                        case RelativeTimeVal::UNIT_HOUR:  $time->modify("{$sign}{$num} hours"); break;
                        case RelativeTimeVal::UNIT_DAY:   $time->modify("{$sign}{$num} days"); break;
                        case RelativeTimeVal::UNIT_WEEK:  $time->modify("{$sign}{$num} weeks"); break;
                        case RelativeTimeVal::UNIT_MONTH:  $time->modify("{$sign}{$num} month"); break;
                        case RelativeTimeVal::UNIT_YEAR:  $time->modify("{$sign}{$num} years"); break;
                    }
                }

                return $time;

            case $queryValue instanceof FuncVal:
                $params = [];
                foreach ($queryValue->params as $p) {
                    $params[] = $this->rawValueFromQueryVal($p, $term, $context);
                }

                return $this->functionCaller(
                    $queryValue->name,
                    $params,
                    $term,
                    $context
                );

            default:
                throw new \InvalidArgumentException("Unknown value type {$queryValue['valueType']}");
        }
    }

    /**
     * @param string $name
     * @param array  $params
     *
     * @return mixed
     */
    public function functionCaller($name, array $params)
    {
        $name = strtolower($name);

        switch ($name) {
            case 'now':
                return $this->fnNow();

            case 'date':
                if (empty($params[0])) {
                    return $this->fnNow();
                }

                return $this->fnDate($params[0]);

            default:
                $nameErr = substr($name, 0, 25);
                throw new \InvalidArgumentException("Function $nameErr() is not a valid value function");
        }
    }

    /**
     * @return string
     */
    public function fnNow()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * @param $dateStr
     *
     * @return \DateTime
     */
    public function fnDate($dateStr)
    {
        return new \DateTime($dateStr);
    }
}
