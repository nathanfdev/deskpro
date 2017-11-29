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

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Component\Util\ListUtils;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ValueCheckHelper
{
    public static function checkValue($fieldValue, $operator, $checkValue)
    {
        $isNot = substr($operator, 0, 3) === 'NOT';

        switch ($operator) {
            case '=':
                return $fieldValue == $checkValue;

            case '!=':
                return $fieldValue == $checkValue;

            case '<':
                return $fieldValue < $checkValue;

            case '<=':
                return $fieldValue <= $checkValue;

            case '>':
                return $fieldValue > $checkValue;

            case '>=':
                return $fieldValue >= $checkValue;

            case 'IN':
            case 'NOT_IN':
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

                if ($isNot) {
                    $res = !$res;
                }

                return $res;

            case 'BETWEEN':
            case 'NOT_BETWEEN':
                if (!isset($checkValue[0]) || !isset($checkValue[1])) {
                    return false;
                }

                $res = $fieldValue >= $checkValue[0] and $fieldValue <= $checkValue[1];

                if ($isNot) {
                    $res = !$res;
                }

                return $res;

            case 'IS_NULL':
            case 'NOT_NULL':
                $res = $fieldValue === null || $fieldValue === 0 || $fieldValue === '0';
                if ($isNot) {
                    $res = !$res;
                }

                return $res;

            case 'IS_EMPTY':
            case 'NOT_EMPTY':
                $res = empty($fieldValue);
                if ($isNot) {
                    $res = !$res;
                }

                return $res;

            case 'EXISTS':
            case 'NOT_EXISTS':
                $res = !empty($fieldValue);
                if ($isNot) {
                    $res = !$res;
                }

                return $res;
        }
    }

    /**
     * @param array         $term
     * @param array|null    $context
     * @param callable|null $functionCaller
     *
     * @return mixed
     */
    public static function checkFromTermNode($fieldValue, array $term, array $context = null, callable $functionCaller = null)
    {
        switch ($term['operator']) {
            case '=':
            case '!=':
            case '<':
            case '>':
            case '<=':
            case '>=':
            case '<>':
            case 'IS_NULL':
            case 'NOT_NULL':
            case 'EMPTY':
            case 'NOT_EMPTY':
            case 'EXISTS':
            case 'NOT_EXISTS':
                $value = self::valueFromValueNode($term['options']['value'], $context, $functionCaller);
            break;

            case 'IN':
            case 'NOT_IN':
                if (isset($term['options']['valueList'])) {
                    $value = self::valueFromValueNode($term['options']['valueList'], $context, $functionCaller);
                } else {
                    $value = self::valueFromValueNode($term['options']['value'], $context, $functionCaller);
                }
                break;

            case 'BETWEEN':
            case 'NOT_BETWEEN':
                return [
                    'value1' => self::valueFromValueNode($term['options']['value1']),
                    'value2' => self::valueFromValueNode($term['options']['value2']),
                ];

            default:
                throw new \InvalidArgumentException("Unknown operator {$term['operator']}");
        }

        return self::checkValue($fieldValue, $term['operator'], $value);
    }

    /**
     * @param array         $valueNode
     * @param array|null    $context
     * @param callable|null $functionCaller
     *
     * @return mixed
     */
    public static function valueFromValueNode(array $valueNode, array $context = null, callable $functionCaller = null)
    {
        if ($valueNode['type'] !== 'VAL') {
            throw new \InvalidArgumentException('Only VAL nodes have a value');
        }

        switch ($valueNode['valueType']) {
            case 'BOOLEAN':
            case 'NUMERIC':
            case 'STRING':
                return $valueNode['value'];

            case 'VAR':
                if (!$context) {
                    throw new \RuntimeException('No context was provided');
                }
                $accessor = PropertyAccess::createPropertyAccessor();

                return $accessor->getValue($context, $valueNode['identity']);

            case 'FUNC':
                if (!$functionCaller) {
                    throw new \RuntimeException('No functionCaller was provided');
                }
                $params = [];
                foreach ($valueNode['params'] as $p) {
                    $params[] = self::valueFromValueNode($p, $context, $functionCaller);
                }

                return $functionCaller($valueNode['name'], $params, $context);

            default:
                throw new \InvalidArgumentException("Unknown value type {$valueNode['valueType']}");
        }
    }
}
