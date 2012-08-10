%declare_class {class Parser}
%token_prefix T_

%syntax_error
{
	throw new \Exception('Parsing error');
}

%include_class
{
	protected $_result = null;

	public function getResult()
	{
		return $this->_result;
	}

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
%left OP_EQ OP_NE OP_GT OP_GTEQ OP_LT OP_LTEQ OP_IN OP_LIKE .
%left OP_MINUS OP_PLUS .
%left OP_MULTIPLY OP_DIVIDE .
%right OP_U_MINUS .
%right OP_BANG .



start ::= display_query trailing_semicolon .


trailing_semicolon ::= SEMICOLON .
trailing_semicolon ::= .



display_query ::= display_clause(A) select_clause(B) from_clause(C) where_clause(D)
	split_clause(E) group_clause(F) order_clause(G) limit_clause(H) .
{
	$res = new Statement\Display(A, B, C);

	if (D) {
		$res->setWhere(D);
	}
	if (E) {
		$res->setSplitBy(E);
	}
	if (F) {
		$res->setGroupBy(F);
	}
	if (G) {
		$res->setOrderBy(G);
	}
	if (H) {
		$res->setLimitAmount(H['limit']);
		if (isset(H['offset'])) {
			$res->setLimitOffset(H['offset']);
		}
	}

	$this->_result = $res;
}



display_clause(res) ::= DISPLAY TABLE .
{
	res = 'table';
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



select_field(res) ::= expression(A) alias_optional(B) .
{
	if (B) {
		res = new Statement\Part\Alias(A, B);
	} else {
		res = A;
	}
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



where_clause(res) ::= WHERE expression(A) .
{
	res = A;
}
where_clause ::= .



split_clause(res) ::= SPLIT BY expression(A) comma_expressions_opt(B) .
{
	res = array(A);
	if (B) {
		res = array_merge(res, B);
	}
}
split_clause ::= .



group_clause(res) ::= GROUP BY expression(A) comma_expressions_opt(B) .
{
	res = array(A);
	if (B) {
		res = array_merge(res, B);
	}
}
group_clause ::= .



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
		res = new Statement\Part\OrderDir(A, B);
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



expression(res) ::= expression(A) OP_EQ|OP_NE|OP_GT|OP_GTEQ|OP_LT|OP_LTEQ(B) expression(C) .
{
	// this line should be = @B, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	res = new Statement\Part\BinaryComparison($token, A, C);
}

expression(res) ::= expression(A) OP_OR|OP_AND(B) expression(C) .
{
	// this line should be = @B, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	res = new Statement\Part\BinaryLogical($token, A, C);
}

expression(res) ::= expression(A) OP_MINUS|OP_PLUS|OP_MULTIPLY|OP_DIVIDE(B) expression(C) .
{
	// this line should be = @B, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	res = new Statement\Part\BinaryMath($token, A, C);
}

expression(res) ::= expression(A) OP_LIKE expression(B) .
{
	res = new Statement\Part\Like(A, B);
}

expression(res) ::= expression(A) OP_NOT OP_LIKE expression(B) .
{
	res = new Statement\Part\Like(A, B, false);
}

expression(res) ::= expression(A) OP_IN LEFT_PAREN expression(B) comma_expressions_opt(C) RIGHT_PAREN .
{
	$values = array(B);
	if (C) {
		$values = array_merge($values, C);
	}
	res = new Statement\Part\In(A, $values);
}

expression(res) ::= expression(A) OP_NOT OP_IN LEFT_PAREN expression(B) comma_expressions_opt(C) RIGHT_PAREN .
{
	$values = array(B);
	if (C) {
		$values = array_merge($values, C);
	}
	res = new Statement\Part\In(A, $values);
}

expression(res) ::= OP_MINUS(A) expression(B) . [OP_U_MINUS]
{
	// this line should be = @A, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	res = new Statement\Part\UnaryOperator($token, B);
}

expression(res) ::= OP_BANG|OP_NOT(A) expression(B) .
{
	// this line should be = @A, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	res = new Statement\Part\UnaryOperator($token, B);
}

expression(res) ::= LEFT_PAREN expression(A) RIGHT_PAREN .
{
	res = A;
}

expression(res) ::= LITERAL(A) LEFT_PAREN func_args(B) RIGHT_PAREN .
{
	if (!B) {
		B = array();
	}
	res = new Statement\Part\FunctionCall(A, B);
}

expression(res) ::= COLUMN(A) .
{
	res = new Statement\Part\Column(explode('.', A));
}

expression(res) ::= LITERAL(A) .
{
	res = new Statement\Part\String(A);
}

expression(res) ::= QUOTED(A) .
{
	res = new Statement\Part\String($this->processQuoted(A));
}

expression(res) ::= PLACEHOLDER(A) .
{
	$value = substr(A, 1, -1);
	res = new Statement\Part\Placeholder($value);
}

expression(res) ::= AT LITERAL(A) .
{
	res = new Statement\Part\AliasRef(A);
}

expression(res) ::= AT QUOTED(A) .
{
	res = new Statement\Part\AliasRef($this->processQuoted(A));
}

expression(res) ::= NUMBER(A) .
{
	res =  new Statement\Part\Number(A + 0);
}

expression(res) ::= NULL .
{
	res = new Statement\Part\NullValue();
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