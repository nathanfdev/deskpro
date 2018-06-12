<?php

namespace DeskPRO\Component\FilterQueryLanguage;

/**
 * Class QueryUtil.
 */
class QueryUtil
{
    /**
     * Escape a string value.
     *
     * @param string $value
     *
     * @return string
     */
    public static function escapeValue($value)
    {
        return str_replace(
            ['"', "'"],
            ['""', '""'],
            $value
        );
    }

    /**
     * Quote a value for use in a query string.
     *
     * @param string $value
     *
     * @return string
     */
    public static function quoteValue($value)
    {
        return '"'.self::escapeValue($value).'"';
    }

    /**
     * @param string $value
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function parseDateFieldFromQuery($value)
    {
        if (preg_match('/(.+)(\/|--)(.+)/', $value, $matches)) {
            $from = self::parseDate($matches[1]);
            $to   = self::parseDate($matches[3]);

            if (!preg_match('/[T:]/', $matches[3])) {
                $datetime = new \DateTime($to);
                $datetime->modify('midnight 23:59:59');

                $to = $datetime->format('c');
            }

            $op          = 'BETWEEN';
            $parsedValue = self::quoteValue($from).' AND '.self::quoteValue($to);
        } elseif (preg_match('/(>=|<=|<|>)(.+)/', $value, $matches)) {
            $op          = $matches[1];
            $parsedValue = self::quoteValue(self::parseDate($matches[2]));
        } else {
            $op          = '>=';
            $parsedValue = self::quoteValue(self::parseDate($value));
        }

        return [$op, $parsedValue];
    }

    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function parseDate($value)
    {
        try {
            $datetime = new \DateTime($value);
        } catch (\Exception $e) {
            try {
                $datetime = new \DateTime('@'.$value);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Unable to parse datetime value $value");
            }
        }

        return $datetime->format('c');
    }
}
