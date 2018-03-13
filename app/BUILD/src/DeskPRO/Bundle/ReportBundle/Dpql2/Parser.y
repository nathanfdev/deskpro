%declare_class {class Parser}
%token_prefix T_

%syntax_error
{
	throw new DpqlException("Error parsing DPQL statement at line $this->line (got $TOKEN)");
}

%include_class
{
	/**
	 * Line number currently being parsed. This comes from the lexer.
	 *
	 * @var integer
	 */
	public $line = 1;

    /**
     * @var DpqlStatementFactory
     */
    protected $statementFactory;

	/**
	 * The output of parsing. When parsing has run, this will be a statement object.
	 *
	 * @var Statement\Query|null
	 */
	protected $_result = null;

    /**
     * Constructor.
     *
     * @param DpqlStatementFactory $statementFactory
     */
    public function __construct(DpqlStatementFactory $statementFactory)
    {
        $this->statementFactory = $statementFactory;
    }

	/**
	 * Gets the result object.
	 *
	 * @return Statement\Query|null
	 */
	public function getResult()
	{
		return $this->_result;
	}

	/**
	 * Processes a quoted string, by removing the quotes and un-escaping
	 * backslashes.
	 *
	 * @param string $string Quoted string
	 *
	 * @return string String with quotes/escaping removed.
	 */
	public function processQuoted($string)
	{
		if (!strlen($string)) {
			return $string;
		}
		$firstChar = $string[0];
		if (substr($string, -1) !== $firstChar) {
			return $string; // not quoted properly
		}

		$string = substr($string, 1, -1); // strip off quotes

		$searchPos = 0;
		do {
			$searchPos = strpos($string, '\\', $searchPos);
			if ($searchPos === false) {
				break;
			}

			// strip out the back slash and step 1 forward to skip the character after (what it escaped)
			$string = substr($string, 0, $searchPos) . substr($string, $searchPos + 1);
			$searchPos++;
		} while (true);

		return $string;
	}
}



%left OP_OR .
%left OP_AND .
%right OP_NOT .
%left OP_EQ OP_NE OP_GT OP_GTEQ OP_LT OP_LTEQ OP_IN OP_LIKE OP_REGEXP .
%left OP_MINUS OP_PLUS .
%left OP_MULTIPLY OP_DIVIDE .
%right OP_U_MINUS .
%right OP_BANG .



start ::= select_query_part(A) trailing_semicolon .
{
    $this->_result = A;
}

trailing_semicolon ::= SEMICOLON .
trailing_semicolon ::= .

select_paren(res) ::= LEFT_PAREN select_query_part(A) RIGHT_PAREN .
{
    res = A;
}

select_union_paren(res) ::= select_paren(A) .
{
    A->setIsUnionPart(true);

    res = A;
}

select_union(res) ::= select_union_paren(A) select_union(B) trailing_semicolon .
{
    res = array(array('ANY', A));
    if (B) {
        res = array_merge(res, B);
    }
}

select_union(res) ::= select_union(A) OP_UNION select_union_paren(B) .
{
    if (!A) {
        res = array();
    } else {
        res = A;
    }

    res[] = array('DISTINCT', B);
}

select_union(res) ::= select_union(A) OP_UNION OP_ALL select_union_paren(B) .
{
    if (!A) {
        res = array();
    } else {
        res = A;
    }

    res[] = array('ANY', B);
}

select_union(res) ::= select_union(A) OP_UNION OP_DISTINCT select_union_paren(B) .
{
    if (!A) {
        res = array();
    } else {
        res = A;
    }

    res[] = array('DISTINCT', B);
}

select_union ::= .

select_query_part(res) ::= select_clause(B) from_clause(C) where_clause(D)
	split_clause(E) group_clause(F) with_rollup_clause(ROLLUP) order_clause(G) limit_clause(H) .
{
	$q = $this->statementFactory->createSelectPart(B, C);

	if (D) {
		$q->setWhere(D);
	}
	if (E) {
		$q->setSplitBy(E);
	}
	if (F) {
		$q->setGroupBy(F);
	}
	if (G) {
		$q->setOrderBy(G);
	}
	if (H) {
		$q->setLimitAmount(H['limit']);
		if (isset(H['offset'])) {
			$q->setLimitOffset(H['offset']);
		}
	}
	if (ROLLUP) {
	    $q->setWithRollup(true);
	}

	res = $q;
}

select_subquery_part(res) ::= LEFT_PAREN select_clause(B) from_clause(C) where_clause(D) order_clause(G) RIGHT_PAREN .
{
	$q = $this->statementFactory->createSelectPart(B, C);

	if (D) {
		$q->setWhere(D);
	}
	if (G) {
		$q->setOrderBy(G);
	}

    $q->setIsSubQuery(true);

	res = $q;
}

select_clause(res) ::= SELECT select_field(A) select_fields_extra(B) .
{
	res = array(A);
	if (B) {
		res = array_merge(res, B);
	}
}


select_fields_extra(res) ::= select_fields_extra(A) COMMA select_field(B) .
{
	if (!A) {
		res = array();
	} else {
		res = A;
	}
	res[] = B;
}
select_fields_extra ::= .

select_field(res) ::= select_subquery_part(A) alias_optional(B) .
{
    if (B) {
		res = $this->statementFactory->createAlias(A, B);
	} else {
		res = A;
	}
}

select_field(res) ::= expression(A) alias_optional(B) .
{
	if (B) {
		res = $this->statementFactory->createAlias(A, B);
	} else {
		res = A;
	}
}

select_field(res) ::= COLUMN_STAR(A) .
{
	res = $this->statementFactory->createColumnStar(explode('.', A));
}



alias_optional(res) ::= AS LITERAL(A) .
{
	res = A;
}
alias_optional(res) ::= AS QUOTED(A) .
{
	res = $this->processQuoted(A);
}
alias_optional ::= .



from_clause(res) ::= FROM LITERAL(A) .
{
	res = A;
}

from_clause(res) ::= FROM select_subquery_part(A) alias_optional(B) .
{
    res = $this->statementFactory->createAlias(A, B);
}

from_clause(res) ::= FROM LEFT_PAREN select_union(A) RIGHT_PAREN alias_optional(B) .
{
    res = $this->statementFactory->createAlias($this->statementFactory->createUnion(A), B);
}

from_clause(res) ::= FROM LEFT_PAREN LITERAL(A) RIGHT_PAREN alias_optional(B) .
{
    res = $this->statementFactory->createAlias(A, B);
}



where_clause(res) ::= WHERE expression(A) .
{
	res = A;
}

where_clause ::= .

split_clause(res) ::= SPLIT BY split_expression(A) split_expressions_extra(B) .
{
	res = (A ? array(A) : array());
	if (B) {
		res = array_merge(res, B);
	}
}
split_clause ::= .



split_expressions_extra(res) ::= split_expressions_extra(A) COMMA split_expression(B) .
{
	if (!A) {
		res = array();
	} else {
		res = A;
	}

	if (B) {
		res[] = B;
	}
}
split_expressions_extra ::= .



split_expression(res) ::= expression(A) .
{
	if (A instanceof Statement\Part\NullValue) {
		res = false;
	} else {
		res = A;
	}
}



group_clause(res) ::= GROUP BY group_expression(A) group_expressions_extra(B) .
{
	res = (A ? array(A) : array());
	if (B) {
		res = array_merge(res, B);
	}
}
group_clause ::= .


group_expressions_extra(res) ::= group_expressions_extra(A) COMMA group_expression(B) .
{
	if (!A) {
		res = array();
	} else {
		res = A;
	}

	if (B) {
		res[] = B;
	}
}
group_expressions_extra ::= .



group_expression(res) ::= expression(A) alias_optional(B) .
{
	if (A instanceof Statement\Part\NullValue) {
		res = false;
	} else if (B) {
		res = $this->statementFactory->createAlias(A, B);
	} else {
		res = A;
	}
}



with_rollup_clause(res) ::= WITH ROLLUP .
{
    res = true;
}
with_rollup_clause(res) ::= .



order_clause(res) ::= ORDER BY order_expression(A) comma_order_expression_opt(B) .
{
	res = array(A);
	if (B) {
		res = array_merge(res, B);
	}
}
order_clause ::= .



order_expression(res) ::= expression(A) direction_opt(B) .
{
	if (B) {
		res = $this->statementFactory->createOrderDir(A, B);
	} else {
		res = A;
	}
}



direction_opt(res) ::= ASC .
{
	res = 'ASC';
}

direction_opt(res) ::= DESC .
{
	res = 'DESC';
}

direction_opt ::= .



comma_order_expression_opt(res) ::= comma_order_expression_opt(A) COMMA order_expression(B) .
{
	if (!A) {
		res = array();
	} else {
		res = A;
	}
	res[] = B;
}
comma_order_expression_opt ::= .



limit_clause(res) ::= LIMIT NUMBER(A) limit_offset_opt(B) .
{
	res = array('limit' => intval(A));
	if (B) {
		res['offset'] = B;
	}
}
limit_clause ::= .



limit_offset_opt(res) ::= OFFSET NUMBER(A) .
{
	res = intval(A);
}
limit_offset_opt ::= .

expression(res) ::= select_subquery_part(B) .
{
    res = $this->statementFactory->createSubSelect(B);
}

expression(res) ::= OP_EXISTS select_subquery_part(B) .
{
    res = $this->statementFactory->createExists(B);
}

expression(res) ::= expression(A) OP_EQ|OP_NE|OP_GT|OP_GTEQ|OP_LT|OP_LTEQ(B) expression(C) .
{
	// this line should be = @B, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	res = $this->statementFactory->createBinaryComparison($token, A, C);
}

expression(res) ::= expression(A) OP_OR|OP_AND(B) expression(C) .
{
	// this line should be = @B, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	res = $this->statementFactory->createBinaryLogical($token, A, C);
}

expression(res) ::= expression(A) OP_MINUS|OP_PLUS(B) interval_expression(C) .
{
	// this line should be = @B, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;
	$expression = C;

	if ($expression[0] == 'interval') {
		res = $this->statementFactory->createBinaryInterval($token, A, $expression[1], $expression[2]);
	} else {
		res = $this->statementFactory->createBinaryMath($token, A, $expression[1]);
	}
}

interval_expression(res) ::= INTERVAL NUMBER(A) LITERAL(B) .
{
	res = array('interval', A, B);
}

interval_expression(res) ::= expression(A) .
{
	res = array('expression', A);
}

expression(res) ::= expression(A) OP_MULTIPLY|OP_DIVIDE(B) expression(C) .
{
	// this line should be = @B, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	res = $this->statementFactory->createBinaryMath($token, A, C);
}

expression(res) ::= expression(A) OP_LIKE expression(B) .
{
	res = $this->statementFactory->createLike(A, B);
}

expression(res) ::= expression(A) OP_NOT OP_LIKE expression(B) .
{
	res = $this->statementFactory->createLike(A, B, false);
}

expression(res) ::= expression(A) OP_REGEXP expression(B) .
{
	res = $this->statementFactory->createRegExp(A, B);
}

expression(res) ::= expression(A) OP_NOT OP_REGEXP expression(B) .
{
	res = $this->statementFactory->createRegExp(A, B, false);
}

expression(res) ::= expression(A) OP_IN LEFT_PAREN expression(B) comma_expressions_opt(C) RIGHT_PAREN .
{
	$values = array(B);
	if (C) {
		$values = array_merge($values, C);
	}
	res = $this->statementFactory->createIn(A, $values);
}

expression(res) ::= expression(A) OP_NOT OP_IN LEFT_PAREN expression(B) comma_expressions_opt(C) RIGHT_PAREN .
{
	$values = array(B);
	if (C) {
		$values = array_merge($values, C);
	}
	res = $this->statementFactory->createIn(A, $values, false);
}

expression(res) ::= expression(A) OP_IN select_subquery_part(B) .
{
    res = $this->statementFactory->createInSubquery(A, B);
}

expression(res) ::= expression(A) OP_NOT OP_IN select_subquery_part(B) .
{
    res = $this->statementFactory->createInSubquery(A, B, false);
}

expression(res) ::= OP_MINUS(A) expression(B) . [OP_U_MINUS]
{
	// this line should be = @A, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	res = $this->statementFactory->createUnaryOperator($token, B);
}

expression(res) ::= OP_BANG|OP_NOT(A) expression(B) .
{
	// this line should be = @A, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	res = $this->statementFactory->createUnaryOperator($token, B);
}

expression(res) ::= LEFT_PAREN expression(A) RIGHT_PAREN .
{
	res = $this->statementFactory->createParentheses(A);
}

expression(res) ::= LITERAL(A) LEFT_PAREN func_args(B) RIGHT_PAREN .
{
	if (!B) {
		B = array();
	}
	res = $this->statementFactory->createFunctionCall(A, B);
}

expression(res) ::= COLUMN(A) .
{
	res = $this->statementFactory->createColumn(explode('.', A));
}

expression(res) ::= LITERAL(A) .
{
	res = $this->statementFactory->createStringPart(A);
}

expression(res) ::= QUOTED(A) .
{
	res = $this->statementFactory->createStringPart($this->processQuoted(A));
}

expression(res) ::= PLACEHOLDER(A) .
{
	$value = substr(A, 1, -1);
	res = $this->statementFactory->createPlaceholder($value);
}

expression(res) ::= VARIABLE(A) .
{
	$value = substr(A, 2, -1);
	res = $this->statementFactory->createVariable($value);
}

expression(res) ::= AT LITERAL(A) .
{
	res = $this->statementFactory->createAliasRef(A);
}

expression(res) ::= AT QUOTED(A) .
{
	res = $this->statementFactory->createAliasRef($this->processQuoted(A));
}

expression(res) ::= NUMBER(A) .
{
	res = $this->statementFactory->createNumber(A + 0);
}

expression(res) ::= NULL .
{
	res = $this->statementFactory->createNullValue();
}

func_args(res) ::= expression(A) comma_expressions_opt(B) .
{
	res = array(A);
	if (B) {
		res = array_merge(res, B);
	}
}
func_args ::= .



comma_expressions_opt(res) ::= comma_expressions_opt(A) COMMA expression(B) .
{
	if (!A) {
		res = array();
	} else {
		res = A;
	}
	res[] = B;
}
comma_expressions_opt ::= .