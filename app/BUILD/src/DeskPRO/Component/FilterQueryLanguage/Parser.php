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

use Doctrine\ORM\Query\AST;

class Parser
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

        return $expr;
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

    /**
     * Parses an arbitrary path expression and defers semantical validation
     * based on expected types.
     *
     * PathExpression ::= IdentificationVariable {"." identifier}*
     *
     * @param int $expectedTypes
     *
     * @return \Doctrine\ORM\Query\AST\PathExpression
     */
    public function IdentificationVariable()
    {
        $this->match(Lexer::T_IDENTIFIER);
        $identVariable = $this->lexer->token['value'];

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

    /**
     * NewValue ::= SimpleArithmeticExpression | StringPrimary | DatetimePrimary | BooleanPrimary |
     *      EnumPrimary | SimpleEntityExpression | "NULL".
     *
     * NOTE: Since it is not possible to correctly recognize individual types, here is the full
     * grammar that needs to be supported:
     *
     * NewValue ::= SimpleArithmeticExpression | "NULL"
     *
     * SimpleArithmeticExpression covers all *Primary grammar rules and also SimpleEntityExpression
     *
     * @return AST\ArithmeticExpression
     */
    public function NewValue()
    {
        if ($this->lexer->isNextToken(Lexer::T_NULL)) {
            $this->match(Lexer::T_NULL);

            return null;
        }

        if ($this->lexer->isNextToken(Lexer::T_INPUT_PARAMETER)) {
            $this->match(Lexer::T_INPUT_PARAMETER);

            return [
                'valueType' => 'VAR',
                'identity'  => $this->lexer->token['value'],
            ];
        }

        return $this->ArithmeticExpression();
    }

    /**
     * NewObjectArg ::= ScalarExpression | "(" Subselect ")".
     *
     * @return mixed
     */
    public function NewObjectArg()
    {
        return $this->ScalarExpression();
    }

    /**
     * ScalarExpression ::= SimpleArithmeticExpression | StringPrimary | DateTimePrimary |
     *                      StateFieldPathExpression | BooleanPrimary | CaseExpression |
     *                      InstanceOfExpression.
     *
     * @return mixed One of the possible expressions or subexpressions
     */
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
                    'valueType' => 'BOOLEAN',
                    'value'     => $lookahead === Lexer::T_TRUE ? true : false,
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

    /**
     * ConditionalExpression ::= ConditionalTerm {"OR" ConditionalTerm}*.
     *
     * @return \Doctrine\ORM\Query\AST\ConditionalExpression
     */
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
            'type'     => 'BOOL',
            'operator' => 'OR',
            'terms'    => $conditionalTerms,
        ];
    }

    /**
     * ConditionalTerm ::= ConditionalFactor {"AND" ConditionalFactor}*.
     *
     * @return \Doctrine\ORM\Query\AST\ConditionalTerm
     */
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
            'type'     => 'BOOL',
            'operator' => 'AND',
            'terms'    => $conditionalFactors,
        ];
    }

    /**
     * ConditionalFactor ::= ["NOT"] ConditionalPrimary.
     *
     * @return \Doctrine\ORM\Query\AST\ConditionalFactor
     */
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
            'type'     => 'BOOL',
            'operator' => 'NOT',
            'terms'    => [
                $conditionalPrimary,
            ],
        ];
    }

    /**
     * ConditionalPrimary ::= SimpleConditionalExpression | "(" ConditionalExpression ")".
     *
     * @return \Doctrine\ORM\Query\AST\ConditionalPrimary
     */
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

    /**
     * SimpleConditionalExpression ::=
     *      ComparisonExpression | BetweenExpression | LikeExpression |
     *      InExpression | NullComparisonExpression | ExistsExpression |
     *      EmptyCollectionComparisonExpression | CollectionMemberExpression |
     *      InstanceOfExpression.
     */
    public function SimpleConditionalExpression()
    {
        if ($this->lexer->isNextToken(Lexer::T_EXISTS)) {
            return $this->ExistsExpression();
        }

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

        return $this->ComparisonExpression();
    }

    /**
     * EmptyCollectionComparisonExpression ::= CollectionValuedPathExpression "IS" ["NOT"] "EMPTY".
     *
     * @return \Doctrine\ORM\Query\AST\EmptyCollectionComparisonExpression
     */
    public function EmptyCollectionComparisonExpression()
    {
        $emptyCollectionCompExpr = new AST\EmptyCollectionComparisonExpression(
            $this->IdentificationVariable()
        );
        $this->match(Lexer::T_IS);

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $emptyCollectionCompExpr->not = true;
        }

        $this->match(Lexer::T_EMPTY);

        return $emptyCollectionCompExpr;
    }

    /**
     * Literal ::= string | char | integer | float | boolean.
     *
     * @return \Doctrine\ORM\Query\AST\Literal
     */
    public function Literal($identifierAsString = false)
    {
        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_STRING:
                $this->match(Lexer::T_STRING);

                return [
                    'valueType' => 'STRING',
                    'value'     => $this->lexer->token['value'],
                ];

            case Lexer::T_IDENTIFIER:
                if ($identifierAsString) {
                    $this->match(Lexer::T_IDENTIFIER);

                    return [
                        'valueType' => 'STRING',
                        'value'     => $this->lexer->token['value'],
                    ];
                }
                break;

            case Lexer::T_INTEGER:
            case Lexer::T_FLOAT:
                $this->match(
                    $this->lexer->isNextToken(Lexer::T_INTEGER) ? Lexer::T_INTEGER : Lexer::T_FLOAT
                );

                return [
                    'valueType' => 'NUMERIC',
                    'value'     => $this->lexer->token['value'],
                ];

            case Lexer::T_TRUE:
            case Lexer::T_FALSE:
                $this->match(
                    $this->lexer->isNextToken(Lexer::T_TRUE) ? Lexer::T_TRUE : Lexer::T_FALSE
                );

                return [
                    'valueType' => 'BOOLEAN',
                    'value'     => $this->lexer->token['value'],
                ];
        }

        $this->syntaxError('Literal');
    }

    /**
     * InParameter ::= Literal | InputParameter.
     *
     * @return string | \Doctrine\ORM\Query\AST\InputParameter
     */
    public function InParameter()
    {
        if ($this->lexer->lookahead['type'] == Lexer::T_INPUT_PARAMETER) {
            return $this->InputParameter();
        }

        return $this->Literal(true);
    }

    /**
     * InputParameter ::= PositionalParameter | NamedParameter.
     *
     * @return \Doctrine\ORM\Query\AST\InputParameter
     */
    public function InputParameter()
    {
        $this->match(Lexer::T_INPUT_PARAMETER);
        $identVariable = $this->lexer->token['value'];

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
        ];

        return $pathExpr;
    }

    /**
     * ArithmeticExpression ::= SimpleArithmeticExpression | "(" Subselect ")".
     *
     * @return \Doctrine\ORM\Query\AST\ArithmeticExpression
     */
    public function ArithmeticExpression($identifierAsString = false)
    {
        return $this->ArithmeticPrimary($identifierAsString);
    }

    /**
     * ArithmeticPrimary ::= SingleValuedPathExpression | Literal | ParenthesisExpression
     *          | FunctionsReturningNumerics | AggregateExpression | FunctionsReturningStrings
     *          | FunctionsReturningDatetime | IdentificationVariable | ResultVariable
     *          | InputParameter | CaseExpression.
     */
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

    /**
     * StringExpression ::= StringPrimary | ResultVariable | "(" Subselect ")".
     *
     * @return \Doctrine\ORM\Query\AST\StringPrimary |
     *                                               \Doctrine\ORM\Query\AST\Subselect |
     *                                               string
     */
    public function StringExpression()
    {
        return $this->StringPrimary();
    }

    /**
     * StringPrimary ::= StateFieldPathExpression | string | InputParameter | FunctionsReturningStrings | AggregateExpression | CaseExpression.
     */
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
                    'valueType' => 'STRING',
                    'value'     => $this->lexer->token['value'],
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

    /**
     * EntityExpression ::= SingleValuedAssociationPathExpression | SimpleEntityExpression.
     *
     * @return \Doctrine\ORM\Query\AST\SingleValuedAssociationPathExpression |
     *                                                                       \Doctrine\ORM\Query\AST\SimpleEntityExpression
     */
    public function EntityExpression()
    {
        $glimpse = $this->lexer->glimpse();

        if ($this->lexer->isNextToken(Lexer::T_IDENTIFIER) && $glimpse['value'] === '.') {
            return $this->IdentificationVariable();
        }

        return $this->SimpleEntityExpression();
    }

    /**
     * SimpleEntityExpression ::= IdentificationVariable | InputParameter.
     *
     * @return string | \Doctrine\ORM\Query\AST\InputParameter
     */
    public function SimpleEntityExpression()
    {
        if ($this->lexer->isNextToken(Lexer::T_INPUT_PARAMETER)) {
            return $this->InputParameter();
        }

        return $this->IdentificationVariable();
    }

    /**
     * BetweenExpression ::= ArithmeticExpression ["NOT"] "BETWEEN" ArithmeticExpression "AND" ArithmeticExpression.
     *
     * @return \Doctrine\ORM\Query\AST\BetweenExpression
     */
    public function BetweenExpression()
    {
        $not = false;

        $var = $this->IdentificationVariable();

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $not = true;
        }

        $this->match(Lexer::T_BETWEEN);
        $arithExpr2 = $this->ArithmeticExpression(true);
        $this->match(Lexer::T_AND);
        $arithExpr3 = $this->ArithmeticExpression(true);

        $betweenExpr = [
            'type'     => 'TERM',
            'field'    => $var,
            'operator' => $not ? 'BETWEEN' : 'NOT_BETWEEN',
            'options'  => ['value1' => $arithExpr2, 'value2' => $arithExpr3],
        ];

        return $betweenExpr;
    }

    /**
     * ComparisonExpression ::= ArithmeticExpression ComparisonOperator ( QuantifiedExpression | ArithmeticExpression ).
     *
     * @return \Doctrine\ORM\Query\AST\ComparisonExpression
     */
    public function ComparisonExpression()
    {
        $this->lexer->glimpse();

        $var       = $this->CompareFieldValue();
        $operator  = $this->ComparisonOperator();
        $rightExpr = $this->ArithmeticExpression(true);

        return [
            'type'     => 'TERM',
            'field'    => $var,
            'operator' => $operator,
            'options'  => ['value' => $rightExpr],
        ];
    }

    /**
     * InExpression ::= SingleValuedPathExpression ["NOT"] "IN" "(" (InParameter {"," InParameter}* | Subselect) ")".
     *
     * @return \Doctrine\ORM\Query\AST\InExpression
     */
    public function InExpression()
    {
        $var = $this->IdentificationVariable();
        $not = false;

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $not = true;
        }

        $this->match(Lexer::T_IN);
        $this->match(Lexer::T_OPEN_PARENTHESIS);

        $literals   = [];
        $literals[] = $this->InParameter();

        while ($this->lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
            $literals[] = $this->InParameter();
        }

        $this->match(Lexer::T_CLOSE_PARENTHESIS);

        return [
            'type'   => 'TERM',
            'field'  => $var,
            'op'     => $not ? 'NOT_IN' : 'IN',
            'values' => $literals,
        ];
    }

    /**
     * NullComparisonExpression ::= (InputParameter | NullIfExpression | CoalesceExpression | AggregateExpression | FunctionDeclaration | IdentificationVariable | SingleValuedPathExpression | ResultVariable) "IS" ["NOT"] "NULL".
     *
     * @return \Doctrine\ORM\Query\AST\NullComparisonExpression
     */
    public function NullComparisonExpression()
    {
        switch (true) {
            case $this->lexer->isNextToken(Lexer::T_INPUT_PARAMETER):
                $this->match(Lexer::T_INPUT_PARAMETER);

                $expr = [
                    'valueType' => 'VAR',
                    'identity'  => $this->lexer->token['value'],
                ];
                break;

            case $this->isFunction():
                $expr = $this->FunctionDeclaration();
                break;

            default:
                $expr = $this->IdentificationVariable();
                break;
        }

        $this->match(Lexer::T_IS);

        $not = false;
        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $not = true;
        }

        $this->match(Lexer::T_NULL);

        return [
            'type'     => 'TERM',
            'field'    => $expr,
            'operator' => $not ? 'NOT_NULL' : 'NULL',
        ];
    }

    /**
     * ComparisonOperator ::= "=" | "<" | "<=" | "<>" | ">" | ">=" | "!=".
     *
     * @return string
     */
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

    /**
     * FunctionDeclaration ::= FunctionsReturningStrings | FunctionsReturningNumerics | FunctionsReturningDatetime.
     *
     * @return \Doctrine\ORM\Query\AST\Functions\FunctionNode
     */
    public function FunctionDeclaration()
    {
        $token    = $this->lexer->lookahead;
        $funcName = strtolower($token['value']);

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
            'valueType' => 'FUNC',
            'name'      => $funcName,
            'params'    => $literals,
        ];
    }
}
