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

namespace DeskPRO\Component\FilterQueryLanguage;

class Lexer extends \Doctrine\Common\Lexer
{
    // All tokens that are not valid identifiers must be < 100
    const T_NONE              = 1;
    const T_INTEGER           = 2;
    const T_STRING            = 3;
    const T_INPUT_PARAMETER   = 4;
    const T_FLOAT             = 5;
    const T_CLOSE_PARENTHESIS = 6;
    const T_OPEN_PARENTHESIS  = 7;
    const T_COMMA             = 8;
    const T_DOT               = 10;
    const T_EQUALS            = 11;
    const T_GREATER_THAN      = 12;
    const T_LOWER_THAN        = 13;
    const T_NEGATE            = 16;

    // All tokens that are also identifiers should be >= 100
    const T_IDENTIFIER = 100;
    const T_ALL        = 101;
    const T_AND        = 102;
    const T_ANY        = 103;
    const T_BETWEEN    = 107;
    const T_EMPTY      = 117;
    const T_EXISTS     = 120;
    const T_FALSE      = 121;
    const T_IN         = 126;
    const T_IS         = 130;
    const T_NOT        = 138;
    const T_NULL       = 139;
    const T_OR         = 142;
    const T_TRUE       = 151;

    /**
     * Creates a new query scanner object.
     *
     * @param string $input A query string
     */
    public function __construct($input)
    {
        $this->setInput($input);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCatchablePatterns()
    {
        return [
            '\$?[a-z_][a-z0-9_]*[a-z0-9_]{1}',
            '(?:[\-\+]?[0-9]+(?:[\.][0-9]+)?)',
            "'(?:''|[^'])*+'",
            '"(?:""|[^"])*+"',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getNonCatchablePatterns()
    {
        return ['\s+', '(.)'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getType(&$value)
    {
        $type = self::T_NONE;

        switch (true) {
            // Recognize numeric values
            case is_numeric($value):
                if (strpos($value, '.') !== false) {
                    return self::T_FLOAT;
                }

                return self::T_INTEGER;

            // Recognize quoted strings
            case $value[0] === "'":
                $value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));

                return self::T_STRING;

            case $value[0] === '"':
                $value = str_replace('""', '"', substr($value, 1, strlen($value) - 2));

                return self::T_STRING;

            // Recognize identifiers
            case ctype_alpha($value[0]) || $value[0] === '_':
                $name = self::class.'::T_'.strtoupper($value);

                if (defined($name)) {
                    $type = constant($name);

                    if ($type > 100) {
                        return $type;
                    }
                }

                return self::T_IDENTIFIER;

            // Recognize input parameters
            case $value[0] === '$':
                $value = substr($value, 1);

                return self::T_INPUT_PARAMETER;

            // Recognize symbols
            case $value === '.': return self::T_DOT;
            case $value === ',': return self::T_COMMA;
            case $value === '(': return self::T_OPEN_PARENTHESIS;
            case $value === ')': return self::T_CLOSE_PARENTHESIS;
            case $value === '=': return self::T_EQUALS;
            case $value === '>': return self::T_GREATER_THAN;
            case $value === '<': return self::T_LOWER_THAN;
            case $value === '!': return self::T_NEGATE;

            // Default
            default:
                // Do nothing
        }

        return $type;
    }
}
