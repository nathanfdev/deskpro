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

/**
 * This actually parses a query. This is meant for internal use only.
 * Use the Parser class as the wrapper around this.
 *
 * @internal
 */
class QueryParser
{
    /**
     * The lexer.
     *
     * @var Lexer
     */
    private $lexer;

    /**
     * The Query to parse.
     *
     * @var Query
     */
    private $query;

    /**
     * Creates a new query parser object.
     *
     * @param Query $query The Query to parse
     */
    public function __construct($query)
    {
        $this->query = $query;
        $this->lexer = new Lexer($query);
    }

    /**
     * Parses a filter query to simple arrays.
     */
    public function parse()
    {
        // Parse & build AST
        $this->lexer->moveNext();

        $expr = $this->ConditionalExpression();

        // Check for end of string
        if ($this->lexer->lookahead !== null) {
            $this->syntaxError('end of string');
        }

        return [
            'fql'   => $this->query,
            'type'  => 'QUERY',
            'query' => $expr,
        ];
    }

    /**
     * Attempts to match the given token with the current lookahead token.
     *
     * If they match, updates the lookahead token; otherwise raises a syntax
     * error.
     *
     * @param int $token The token type
     *
     * @throws QueryException If the tokens don't match
     */
    public function match($token)
    {
        $lookaheadType = $this->lexer->lookahead['type'];

        // short-circuit on first condition, usually types match
        if ($lookaheadType !== $token && $token !== Lexer::T_IDENTIFIER && $lookaheadType <= Lexer::T_IDENTIFIER) {
            $this->syntaxError($this->lexer->getLiteral($token));
        }

        $this->lexer->moveNext();
    }

    /**
     * Frees this parser, enabling it to be reused.
     *
     * @param bool $deep     Whether to clean peek and reset errors
     * @param int  $position Position to reset
     */
    public function free($deep = false, $position = 0)
    {
        // WARNING! Use this method with care. It resets the scanner!
        $this->lexer->resetPosition($position);

        // Deep = true cleans peek and also any previously defined errors
        if ($deep) {
            $this->lexer->resetPeek();
        }

        $this->lexer->token     = null;
        $this->lexer->lookahead = null;
    }

    /**
     * Generates a new syntax error.
     *
     * @param string     $expected Expected string
     * @param array|null $token    Got token
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function syntaxError($expected = '', $token = null)
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        $tokenPos = (isset($token['position'])) ? $token['position'] : '-1';

        $message = "line 0, col {$tokenPos}: Error: ";
        $message .= ($expected !== '') ? "Expected {$expected}, got " : 'Unexpected ';
        $message .= ($this->lexer->lookahead === null) ? 'end of string.' : "'{$token['value']}'";

        throw QueryException::syntaxError($message, QueryException::queryError($this->query));
    }

    /**
     * Generates a new semantical error.
     *
     * @param string     $message Optional message
     * @param array|null $token   Optional token
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function semanticalError($message = '', $token = null)
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        // Minimum exposed chars ahead of token
        $distance = 12;

        // Find a position of a final word to display in error string
        $dql    = $this->query;
        $length = strlen($dql);
        $pos    = $token['position'] + $distance;
        $pos    = strpos($dql, ' ', ($length > $pos) ? $pos : $length);
        $length = ($pos !== false) ? $pos - $token['position'] : $distance;

        $tokenPos = (isset($token['position']) && $token['position'] > 0) ? $token['position'] : '-1';
        $tokenStr = substr($dql, $token['position'], $length);

        // Building informative message
        $message = 'line 0, col '.$tokenPos." near '".$tokenStr."': Error: ".$message;

        throw QueryException::semanticalError($message, QueryException::queryError($this->query));
    }

    /**
     * Peeks beyond the matched closing parenthesis and returns the first token after that one.
     *
     * @param bool $resetPeek Reset peek after finding the closing parenthesis
     *
     * @return array
     */
    private function peekBeyondClosingParenthesis($resetPeek = true)
    {
        $token        = $this->lexer->peek();
        $numUnmatched = 1;

        while ($numUnmatched > 0 && $token !== null) {
            switch ($token['type']) {
                case Lexer::T_OPEN_PARENTHESIS:
                    ++$numUnmatched;
                    break;

                case Lexer::T_CLOSE_PARENTHESIS:
                    --$numUnmatched;
                    break;

                default:
                    // Do nothing
            }

            $token = $this->lexer->peek();
        }

        if ($resetPeek) {
            $this->lexer->resetPeek();
        }

        return $token;
    }

    /**
     * Checks if the next-next (after lookahead) token starts a function.
     *
     * @return bool TRUE if the next-next tokens start a function, FALSE otherwise
     */
    private function isFunction()
    {
        $lookaheadType = $this->lexer->lookahead['type'];
        $peek          = $this->lexer->peek();

        $this->lexer->resetPeek();

        return $lookaheadType >= Lexer::T_IDENTIFIER && $peek['type'] === Lexer::T_OPEN_PARENTHESIS;
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function IdentificationVariable()
    {
        $this->match(Lexer::T_IDENTIFIER);
        $identVariable = $this->lexer->token['value'];
        $tokenPos      = $this->lexer->token['position'];

        $field = null;

        if ($this->lexer->isNextToken(Lexer::T_DOT)) {
            $this->match(Lexer::T_DOT);
            $this->match(Lexer::T_IDENTIFIER);

            $field = $this->lexer->token['value'];

            while ($this->lexer->isNextToken(Lexer::T_DOT)) {
                $this->match(Lexer::T_DOT);
                $this->match(Lexer::T_IDENTIFIER);
                $field .= '.'.$this->lexer->token['value'];
            }
        }

        // Creating AST node
        $pathExpr = [
            'identity' => $identVariable.($field ? '.'.$field : ''),
            'name'     => $identVariable,
            'path'     => $field ?: null,
            'tokenPos' => $tokenPos,
        ];

        return $pathExpr;
    }

    public function CompareFieldValue()
    {
        $peek = $this->lexer->glimpse();
        if ($peek['type'] === Lexer::T_OPEN_PARENTHESIS) {
            return $this->FunctionDeclaration();
        } else {
            return $this->IdentificationVariable();
        }
    }

    public function ScalarExpression()
    {
        $lookahead = $this->lexer->lookahead['type'];

        switch (true) {
            case $lookahead === Lexer::T_INTEGER:
            case $lookahead === Lexer::T_FLOAT:
                return $this->ArithmeticPrimary();

            case $lookahead === Lexer::T_STRING:
                return $this->StringPrimary();

            case $lookahead === Lexer::T_TRUE:
            case $lookahead === Lexer::T_FALSE:
                $this->match($lookahead);

                return [
                    'type'      => 'VAL',
                    'valueType' => 'BOOLEAN',
                    'value'     => $lookahead === Lexer::T_TRUE ? true : false,
                    'tokenPos'  => $this->lexer->token['position'],
                ];

            case $lookahead === Lexer::T_INPUT_PARAMETER:
                switch (true) {
                    default:
                        return $this->InputParameter();
                }

            case $lookahead === Lexer::T_OPEN_PARENTHESIS:
                return $this->ArithmeticPrimary();

            // this check must be done before checking for a filed path expression
            case $this->isFunction():
                $this->lexer->peek(); // "("
                return $this->FunctionDeclaration();
                break;
            // it is no function, so it must be a field path
            case $lookahead === Lexer::T_IDENTIFIER:
                $this->lexer->peek(); // lookahead => '.'
                $this->lexer->peek(); // lookahead => token after '.'
                $peek = $this->lexer->peek(); // lookahead => token after the token after the '.'
                $this->lexer->resetPeek();

                return $this->IdentificationVariable();

            default:
                $this->syntaxError();
        }
    }

    public function ConditionalExpression()
    {
        $conditionalTerms   = [];
        $conditionalTerms[] = $this->ConditionalTerm();

        while ($this->lexer->isNextToken(Lexer::T_OR)) {
            $this->match(Lexer::T_OR);

            $conditionalTerms[] = $this->ConditionalTerm();
        }

        // Phase 1 AST optimization: Prevent AST\ConditionalExpression
        // if only one AST\ConditionalTerm is defined
        if (count($conditionalTerms) == 1) {
            return $conditionalTerms[0];
        }

        return [
            'type'     => 'TERM_GROUP',
            'operator' => 'OR',
            'terms'    => $conditionalTerms,
        ];
    }

    public function ConditionalTerm()
    {
        $conditionalFactors   = [];
        $conditionalFactors[] = $this->ConditionalFactor();

        while ($this->lexer->isNextToken(Lexer::T_AND)) {
            $this->match(Lexer::T_AND);

            $conditionalFactors[] = $this->ConditionalFactor();
        }

        // Phase 1 AST optimization: Prevent AST\ConditionalTerm
        // if only one AST\ConditionalFactor is defined
        if (count($conditionalFactors) == 1) {
            return $conditionalFactors[0];
        }

        return [
            'type'     => 'TERM_GROUP',
            'operator' => 'AND',
            'terms'    => $conditionalFactors,
        ];
    }

    public function ConditionalFactor()
    {
        $not = false;

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);

            $not = true;
        }

        $conditionalPrimary = $this->ConditionalPrimary();

        // Phase 1 AST optimization: Prevent AST\ConditionalFactor
        // if only one AST\ConditionalPrimary is defined
        if (!$not) {
            return $conditionalPrimary;
        }

        return [
            'type'     => 'TERM_GROUP',
            'operator' => 'NOT',
            'terms'    => [
                $conditionalPrimary,
            ],
        ];
    }

    public function ConditionalPrimary()
    {
        if (!$this->lexer->isNextToken(Lexer::T_OPEN_PARENTHESIS)) {
            return $this->SimpleConditionalExpression();
        }

        // Peek beyond the matching closing parenthesis ')'
        $peek = $this->peekBeyondClosingParenthesis();

        if (in_array($peek['value'], ['=',  '<', '<=', '<>', '>', '>=', '!=']) ||
            in_array($peek['type'], [Lexer::T_NOT, Lexer::T_BETWEEN, Lexer::T_IN, Lexer::T_IS, Lexer::T_EXISTS])) {
            return $this->SimpleConditionalExpression();
        }

        $this->match(Lexer::T_OPEN_PARENTHESIS);
        $conditionalExpression = $this->ConditionalExpression();
        $this->match(Lexer::T_CLOSE_PARENTHESIS);

        return $conditionalExpression;
    }

    public function SimpleConditionalExpression()
    {
        $token     = $this->lexer->lookahead;
        $peek      = $this->lexer->glimpse();
        $lookahead = $token;

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $token = $this->lexer->glimpse();
        }

        if ($token['type'] === Lexer::T_IDENTIFIER || $token['type'] === Lexer::T_INPUT_PARAMETER || $this->isFunction()) {
            // Peek beyond the matching closing parenthesis.
            $beyond = $this->lexer->peek();

            switch ($peek['value']) {
                case '(':
                    // Peeks beyond the matched closing parenthesis.
                    $token = $this->peekBeyondClosingParenthesis(false);

                    if ($token['type'] === Lexer::T_NOT) {
                        $token = $this->lexer->peek();
                    }

                    if ($token['type'] === Lexer::T_IS) {
                        $lookahead = $this->lexer->peek();
                    }
                    break;

                default:
                    // Peek beyond the PathExpression or InputParameter.
                    $token = $beyond;

                    while ($token['value'] === '.') {
                        $this->lexer->peek();

                        $token = $this->lexer->peek();
                    }

                    // Also peek beyond a NOT if there is one.
                    if ($token['type'] === Lexer::T_NOT) {
                        $token = $this->lexer->peek();
                    }

                    // We need to go even further in case of IS (differentiate between NULL and EMPTY)
                    $lookahead = $this->lexer->peek();
            }

            // Also peek beyond a NOT if there is one.
            if ($lookahead['type'] === Lexer::T_NOT) {
                $lookahead = $this->lexer->peek();
            }

            $this->lexer->resetPeek();
        }

        if ($token['type'] === Lexer::T_BETWEEN) {
            return $this->BetweenExpression();
        }

        if ($token['type'] === Lexer::T_IN) {
            return $this->InExpression();
        }

        if ($token['type'] === Lexer::T_IS && $lookahead['type'] === Lexer::T_NULL) {
            return $this->NullComparisonExpression();
        }

        if ($token['type'] === Lexer::T_IS && $lookahead['type'] === Lexer::T_EMPTY) {
            return $this->EmptyCollectionComparisonExpression();
        }

        if ($token['type'] === Lexer::T_EXISTS) {
            return $this->ExistsExpression();
        }

        return $this->ComparisonExpression();
    }

    public function EmptyCollectionComparisonExpression()
    {
        switch (true) {
            case $this->lexer->isNextToken(Lexer::T_INPUT_PARAMETER):
                $expr = $this->InputParameter();
                break;

            case $this->isFunction():
                $expr = $this->FunctionDeclaration();
                break;

            default:
                $expr = $this->IdentificationVariable();
                break;
        }

        $this->match(Lexer::T_IS);
        $tokenPos = $this->lexer->token['position'];

        $not = false;
        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $not = true;
        }

        $this->match(Lexer::T_EMPTY);

        return [
            'type'     => 'TERM',
            'field'    => $expr,
            'operator' => $not ? 'NOT_EMPTY' : 'IS_EMPTY',
            'tokenPos' => $tokenPos,
        ];
    }

    public function ExistsExpression()
    {
        switch (true) {
            case $this->lexer->isNextToken(Lexer::T_INPUT_PARAMETER):
                $expr = $this->InputParameter();
                break;

            case $this->isFunction():
                $expr = $this->FunctionDeclaration();
                break;

            default:
                $expr = $this->IdentificationVariable();
                break;
        }

        $tokenPos = $this->lexer->token['position'];

        $not = false;
        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $not = true;
        }

        $this->match(Lexer::T_EXISTS);

        return [
            'type'     => 'TERM',
            'field'    => $expr,
            'operator' => $not ? 'NOT_EXISTS' : 'EXISTS',
            'tokenPos' => $tokenPos,
        ];
    }

    public function Literal($identifierAsString = false)
    {
        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_STRING:
                $this->match(Lexer::T_STRING);

                return [
                    'type'      => 'VAL',
                    'valueType' => 'STRING',
                    'value'     => $this->lexer->token['value'],
                    'tokenPos'  => $this->lexer->token['position'],
                ];

            case Lexer::T_IDENTIFIER:
                if ($identifierAsString) {
                    $this->match(Lexer::T_IDENTIFIER);

                    return [
                        'type'      => 'VAL',
                        'valueType' => 'STRING',
                        'value'     => $this->lexer->token['value'],
                        'tokenPos'  => $this->lexer->token['position'],
                    ];
                }
                break;

            case Lexer::T_INTEGER:
            case Lexer::T_FLOAT:
                $this->match(
                    $this->lexer->isNextToken(Lexer::T_INTEGER) ? Lexer::T_INTEGER : Lexer::T_FLOAT
                );

                return [
                    'type'      => 'VAL',
                    'valueType' => 'NUMERIC',
                    'value'     => $this->lexer->token['value'],
                    'tokenPos'  => $this->lexer->token['position'],
                ];

            case Lexer::T_TRUE:
            case Lexer::T_FALSE:
                $this->match(
                    $this->lexer->isNextToken(Lexer::T_TRUE) ? Lexer::T_TRUE : Lexer::T_FALSE
                );

                return [
                    'type'      => 'VAL',
                    'valueType' => 'BOOLEAN',
                    'value'     => $this->lexer->token['type'] === Lexer::T_TRUE ? true : false,
                    'tokenPos'  => $this->lexer->token['position'],
                ];

            case Lexer::T_REL_TIME:
                $this->match(Lexer::T_REL_TIME);

                $m       = null;
                $pattern = '#^(?P<sign>[\-\+]{1}(?P<times>(?:[0-9]+(?:[\.][0-9]+)?[hdwmy]{1})+))$#';
                if (!preg_match($pattern, $this->lexer->token['value'], $m)) {
                    $this->semanticalError('Invalid relative date', $this->lexer->token);
                }

                $sign         = $m['sign'];
                $timePartsRaw = preg_split('/(\d\w{1})/', $m['times'], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

                $timeParts = array_map(function ($t) {
                    $num = substr($t, 0, -1);
                    switch (substr($t, -1)) {
                        case 'h': $unit = 'hour'; break;
                        case 'd': $unit = 'day'; break;
                        case 'w': $unit = 'week'; break;
                        case 'm': $unit = 'month'; break;
                        case 'y': $unit = 'year'; break;
                        default: $this->semanticalError('Invalid relative date unit', $this->lexer->token);
                    }

                    return ['unit' => $unit, 'num' => $num];
                }, $timePartsRaw);

                return [
                    'type'      => 'VAL',
                    'valueType' => 'RELATIVE_TIME',
                    'value'     => [
                        'mode'  => $sign === '-' ? 'past' : 'future',
                        'times' => $timeParts,
                    ],
                    'tokenPos' => $this->lexer->token['position'],
                ];
        }

        $this->syntaxError('Literal');
    }

    public function InParameter()
    {
        if ($this->lexer->lookahead['type'] == Lexer::T_INPUT_PARAMETER) {
            return $this->InputParameter();
        }

        return $this->Literal(true);
    }

    public function InputParameter()
    {
        $this->match(Lexer::T_INPUT_PARAMETER);
        $identVariable = $this->lexer->token['value'];
        $tokenPos      = $this->lexer->token['position'];

        $field = null;

        if ($this->lexer->isNextToken(Lexer::T_DOT)) {
            $this->match(Lexer::T_DOT);
            $this->match(Lexer::T_IDENTIFIER);

            $field = $this->lexer->token['value'];

            while ($this->lexer->isNextToken(Lexer::T_DOT)) {
                $this->match(Lexer::T_DOT);
                $this->match(Lexer::T_IDENTIFIER);
                $field .= '.'.$this->lexer->token['value'];
            }
        }

        // Creating AST node
        $pathExpr = [
            'valueType' => 'VAR',
            'identity'  => $identVariable.($field !== null ? '.'.$field : ''),
            'name'      => $identVariable,
            'path'      => $field !== null ? $field : '',
            'tokenPos'  => $tokenPos,
        ];

        return $pathExpr;
    }

    public function ArithmeticExpression($identifierAsString = false)
    {
        return $this->ArithmeticPrimary($identifierAsString);
    }

    public function ArithmeticPrimary($identifierAsString = false)
    {
        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_IDENTIFIER:
                $peek = $this->lexer->glimpse();

                if ($peek['value'] == '(') {
                    return $this->FunctionDeclaration();
                }

                if ($identifierAsString) {
                    return $this->Literal(true);
                } else {
                    if ($peek['value'] == '.') {
                        return $this->IdentificationVariable();
                    }

                    return $this->IdentificationVariable();
                }

            case Lexer::T_INPUT_PARAMETER:
                return $this->InputParameter();

            default:
                $peek = $this->lexer->glimpse();

                if ($peek['value'] == '(') {
                    return $this->FunctionDeclaration();
                }

                return $this->Literal();
        }
    }

    public function StringExpression()
    {
        return $this->StringPrimary();
    }

    public function StringPrimary()
    {
        $lookaheadType = $this->lexer->lookahead['type'];

        switch ($lookaheadType) {
            case Lexer::T_IDENTIFIER:
                $peek = $this->lexer->glimpse();

                if ($peek['value'] == '.') {
                    return $this->IdentificationVariable();
                }

                if ($peek['value'] == '(') {
                    // do NOT directly go to FunctionsReturningString() because it doesn't check for custom functions.
                    return $this->FunctionDeclaration();
                }

                $this->syntaxError("'.' or '('");
                break;

            case Lexer::T_STRING:
                $this->match(Lexer::T_STRING);

                return [
                    'type'      => 'VAL',
                    'valueType' => 'STRING',
                    'value'     => $this->lexer->token['value'],
                    'tokenPos'  => $this->lexer->token['position'],
                ];

            case Lexer::T_INPUT_PARAMETER:
                return $this->InputParameter();

            case Lexer::T_CASE:
            case Lexer::T_COALESCE:
            case Lexer::T_NULLIF:
                return $this->CaseExpression();
        }

        $this->syntaxError(
            'StateFieldPathExpression | string | InputParameter | FunctionsReturningStrings | AggregateExpression'
        );
    }

    public function BetweenExpression()
    {
        $not = false;

        $var = $this->IdentificationVariable();

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $not = true;
        }

        $this->match(Lexer::T_BETWEEN);
        $tokenPos   = $this->lexer->token['position'];
        $arithExpr2 = $this->ArithmeticExpression(true);
        $this->match(Lexer::T_AND);
        $arithExpr3 = $this->ArithmeticExpression(true);

        $betweenExpr = [
            'type'     => 'TERM',
            'field'    => $var,
            'operator' => $not ? 'BETWEEN' : 'NOT_BETWEEN',
            'options'  => ['value1' => $arithExpr2, 'value2' => $arithExpr3],
            'tokenPos' => $tokenPos,
        ];

        return $betweenExpr;
    }

    public function ComparisonExpression()
    {
        $this->lexer->glimpse();

        $var       = $this->CompareFieldValue();
        $tokenPos  = $this->lexer->token['position'];
        $operator  = $this->ComparisonOperator();
        $rightExpr = $this->ArithmeticExpression(true);

        return [
            'type'     => 'TERM',
            'field'    => $var,
            'operator' => $operator,
            'options'  => ['value' => $rightExpr],
            'tokenPos' => $tokenPos,
        ];
    }

    public function InExpression()
    {
        $var = $this->IdentificationVariable();
        $not = false;

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $not = true;
        }

        $this->match(Lexer::T_IN);
        $tokenPos = $this->lexer->token['position'];

        if ($this->lexer->isNextToken(Lexer::T_OPEN_PARENTHESIS)) {
            $this->match(Lexer::T_OPEN_PARENTHESIS);

            $literals   = [];
            $literals[] = $this->InParameter();

            while ($this->lexer->isNextToken(Lexer::T_COMMA)) {
                $this->match(Lexer::T_COMMA);
                $literals[] = $this->InParameter();
            }

            $this->match(Lexer::T_CLOSE_PARENTHESIS);

            return [
                'type'     => 'TERM',
                'field'    => $var,
                'operator' => $not ? 'NOT_IN' : 'IN',
                'options'  => ['valueList' => $literals],
                'tokenPos' => $tokenPos,
            ];
        } else {
            $expr = $this->FunctionDeclaration();

            return [
                'type'     => 'TERM',
                'field'    => $var,
                'operator' => $not ? 'NOT_IN' : 'IN',
                'options'  => ['value' => $expr],
                'tokenPos' => $tokenPos,
            ];
        }
    }

    public function NullComparisonExpression()
    {
        switch (true) {
            case $this->lexer->isNextToken(Lexer::T_INPUT_PARAMETER):
                $expr = $this->InputParameter();
                break;

            case $this->isFunction():
                $expr = $this->FunctionDeclaration();
                break;

            default:
                $expr = $this->IdentificationVariable();
                break;
        }

        $this->match(Lexer::T_IS);
        $tokenPos = $this->lexer->token['position'];

        $not = false;
        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $not = true;
        }

        $this->match(Lexer::T_NULL);

        return [
            'type'     => 'TERM',
            'field'    => $expr,
            'operator' => $not ? 'NOT_NULL' : 'IS_NULL',
            'tokenPos' => $tokenPos,
        ];
    }

    public function ComparisonOperator()
    {
        switch ($this->lexer->lookahead['value']) {
            case '=':
                $this->match(Lexer::T_EQUALS);

                return '=';

            case '<':
                $this->match(Lexer::T_LOWER_THAN);
                $operator = '<';

                if ($this->lexer->isNextToken(Lexer::T_EQUALS)) {
                    $this->match(Lexer::T_EQUALS);
                    $operator .= '=';
                } elseif ($this->lexer->isNextToken(Lexer::T_GREATER_THAN)) {
                    $this->match(Lexer::T_GREATER_THAN);
                    $operator .= '>';
                }

                return $operator;

            case '>':
                $this->match(Lexer::T_GREATER_THAN);
                $operator = '>';

                if ($this->lexer->isNextToken(Lexer::T_EQUALS)) {
                    $this->match(Lexer::T_EQUALS);
                    $operator .= '=';
                }

                return $operator;

            case '!':
                $this->match(Lexer::T_NEGATE);
                $this->match(Lexer::T_EQUALS);

                return '<>';

            default:
                $this->syntaxError('=, <, <=, <>, >, >=, !=');
        }
    }

    public function FunctionDeclaration()
    {
        $token    = $this->lexer->lookahead;
        $funcName = strtolower($token['value']);

        $tokenPos = $this->lexer->token['position'];
        $this->match(Lexer::T_IDENTIFIER);
        $this->match(Lexer::T_OPEN_PARENTHESIS);

        $literals = [];

        if ($this->lexer->lookahead['type'] !== Lexer::T_CLOSE_PARENTHESIS) {
            $literals[] = $this->InParameter();

            while ($this->lexer->isNextToken(Lexer::T_COMMA)) {
                $this->match(Lexer::T_COMMA);
                $literals[] = $this->InParameter();
            }
        }

        $this->match(Lexer::T_CLOSE_PARENTHESIS);

        return [
            'type'      => 'VAL',
            'valueType' => 'FUNC',
            'name'      => $funcName,
            'params'    => $literals,
            'tokenPos'  => $tokenPos,
        ];
    }
}
