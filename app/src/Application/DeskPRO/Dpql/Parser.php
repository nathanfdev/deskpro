<?php

namespace Application\DeskPRO\Dpql;

/* Driver template for the PHP_ParserGenerator parser generator. (PHP port of LEMON)
*/

/**
 * This can be used to store both the string representation of
 * a token, and any useful meta-data associated with the token.
 *
 * meta-data should be stored as an array
 */
class ParseyyToken implements \ArrayAccess
{
    public $string = '';
    public $metadata = array();

    function __construct($s, $m = array())
    {
        if ($s instanceof ParseyyToken) {
            $this->string = $s->string;
            $this->metadata = $s->metadata;
        } else {
            $this->string = (string) $s;
            if ($m instanceof ParseyyToken) {
                $this->metadata = $m->metadata;
            } elseif (is_array($m)) {
                $this->metadata = $m;
            }
        }
    }

    function __toString()
    {
        return $this->string;
    }

    function offsetExists($offset)
    {
        return isset($this->metadata[$offset]);
    }

    function offsetGet($offset)
    {
        return $this->metadata[$offset];
    }

    function offsetSet($offset, $value)
    {
        if ($offset === null) {
            if (isset($value[0])) {
                $x = ($value instanceof ParseyyToken) ?
                    $value->metadata : $value;
                $this->metadata = array_merge($this->metadata, $x);
                return;
            }
            $offset = count($this->metadata);
        }
        if ($value === null) {
            return;
        }
        if ($value instanceof ParseyyToken) {
            if ($value->metadata) {
                $this->metadata[$offset] = $value->metadata;
            }
        } elseif ($value) {
            $this->metadata[$offset] = $value;
        }
    }

    function offsetUnset($offset)
    {
        unset($this->metadata[$offset]);
    }
}

/** The following structure represents a single element of the
 * parser's stack.  Information stored includes:
 *
 *   +  The state number for the parser at this level of the stack.
 *
 *   +  The value of the token stored at this level of the stack.
 *      (In other words, the "major" token.)
 *
 *   +  The semantic value stored at this level of the stack.  This is
 *      the information used by the action routines in the grammar.
 *      It is sometimes called the "minor" token.
 */
class ParseyyStackEntry
{
    public $stateno;       /* The state-number */
    public $major;         /* The major token value.  This is the code
                     ** number for the token at this stack level */
    public $minor; /* The user-supplied minor token value.  This
                     ** is the value of the token  */
};

// code external to the class is included here

// declare_class is output here
#line 1 "Parser.y"
class Parser#line 102 "Parser.php"
{
/* First off, code is included which follows the "include_class" declaration
** in the input file. */
#line 10 "Parser.y"

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
#line 142 "Parser.php"

/* Next is all token values, as class constants
*/
/* 
** These constants (all generated automatically by the parser generator)
** specify the various kinds of tokens (terminals) that the parser
** understands. 
**
** Each symbol here is a terminal symbol in the grammar.
*/
    const T_OP_OR                          =  1;
    const T_OP_AND                         =  2;
    const T_OP_NOT                         =  3;
    const T_OP_EQ                          =  4;
    const T_OP_NE                          =  5;
    const T_OP_GT                          =  6;
    const T_OP_GTEQ                        =  7;
    const T_OP_LT                          =  8;
    const T_OP_LTEQ                        =  9;
    const T_OP_IN                          = 10;
    const T_OP_LIKE                        = 11;
    const T_OP_MINUS                       = 12;
    const T_OP_PLUS                        = 13;
    const T_OP_MULTIPLY                    = 14;
    const T_OP_DIVIDE                      = 15;
    const T_OP_U_MINUS                     = 16;
    const T_OP_BANG                        = 17;
    const T_SEMICOLON                      = 18;
    const T_DISPLAY                        = 19;
    const T_TABLE                          = 20;
    const T_SELECT                         = 21;
    const T_COMMA                          = 22;
    const T_AS                             = 23;
    const T_LITERAL                        = 24;
    const T_QUOTED                         = 25;
    const T_FROM                           = 26;
    const T_WHERE                          = 27;
    const T_SPLIT                          = 28;
    const T_BY                             = 29;
    const T_GROUP                          = 30;
    const T_ORDER                          = 31;
    const T_LIMIT                          = 32;
    const T_NUMBER                         = 33;
    const T_OFFSET                         = 34;
    const T_LEFT_PAREN                     = 35;
    const T_RIGHT_PAREN                    = 36;
    const T_COLUMN                         = 37;
    const T_PLACEHOLDER                    = 38;
    const T_NULL                           = 39;
    const YY_NO_ACTION = 134;
    const YY_ACCEPT_ACTION = 133;
    const YY_ERROR_ACTION = 132;

/* Next are that tables used to determine what action to take based on the
** current state and lookahead token.  These tables are used to implement
** functions that take a state number and lookahead value and return an
** action integer.  
**
** Suppose the action integer is N.  Then the action is determined as
** follows
**
**   0 <= N < self::YYNSTATE                              Shift N.  That is,
**                                                        push the lookahead
**                                                        token onto the stack
**                                                        and goto state N.
**
**   self::YYNSTATE <= N < self::YYNSTATE+self::YYNRULE   Reduce by rule N-YYNSTATE.
**
**   N == self::YYNSTATE+self::YYNRULE                    A syntax error has occurred.
**
**   N == self::YYNSTATE+self::YYNRULE+1                  The parser accepts its
**                                                        input. (and concludes parsing)
**
**   N == self::YYNSTATE+self::YYNRULE+2                  No such action.  Denotes unused
**                                                        slots in the yy_action[] table.
**
** The action table is constructed as a single large static array $yy_action.
** Given state S and lookahead X, the action is computed as
**
**      self::$yy_action[self::$yy_shift_ofst[S] + X ]
**
** If the index value self::$yy_shift_ofst[S]+X is out of range or if the value
** self::$yy_lookahead[self::$yy_shift_ofst[S]+X] is not equal to X or if
** self::$yy_shift_ofst[S] is equal to self::YY_SHIFT_USE_DFLT, it means that
** the action is not in the table and that self::$yy_default[S] should be used instead.  
**
** The formula above is for computing the action when the lookahead is
** a terminal symbol.  If the lookahead is a non-terminal (as occurs after
** a reduce action) then the static $yy_reduce_ofst array is used in place of
** the static $yy_shift_ofst array and self::YY_REDUCE_USE_DFLT is used in place of
** self::YY_SHIFT_USE_DFLT.
**
** The following are the tables generated in this section:
**
**  self::$yy_action        A single table containing all actions.
**  self::$yy_lookahead     A table containing the lookahead for each entry in
**                          yy_action.  Used to detect hash collisions.
**  self::$yy_shift_ofst    For each state, the offset into self::$yy_action for
**                          shifting terminals.
**  self::$yy_reduce_ofst   For each state, the offset into self::$yy_action for
**                          shifting non-terminals after a reduce.
**  self::$yy_default       Default action for each state.
*/
    const YY_SZ_ACTTAB = 175;
static public $yy_action = array(
 /*     0 */    13,   13,   44,   20,   20,   20,   20,   20,   20,   54,
 /*    10 */    14,   15,   15,   15,   15,   13,   13,   44,   20,   20,
 /*    20 */    20,   20,   20,   20,   54,   14,   15,   15,   15,   15,
 /*    30 */    15,   15,   15,   15,   35,   78,    4,   47,   70,   13,
 /*    40 */    13,   44,   20,   20,   20,   20,   20,   20,   54,   14,
 /*    50 */    15,   15,   15,   15,   13,   44,   20,   20,   20,   20,
 /*    60 */    20,   20,   54,   14,   15,   15,   15,   15,   44,   20,
 /*    70 */    20,   20,   20,   20,   20,   54,   14,   15,   15,   15,
 /*    80 */    15,   21,  133,   32,   48,   27,   67,   65,   15,   15,
 /*    90 */    22,   24,   51,   12,   24,   21,    6,   69,   57,    4,
 /*   100 */    60,   56,   58,   83,   49,   85,   73,   71,   76,   25,
 /*   110 */     1,   82,   66,   23,   17,   84,   72,   80,   24,   34,
 /*   120 */    79,   77,    2,   64,   16,   11,   19,   42,  107,  107,
 /*   130 */    28,    8,   53,  107,  107,  107,  107,   59,   63,  107,
 /*   140 */     3,   38,  107,   18,  107,   74,   29,  107,   75,   61,
 /*   150 */    68,  107,   52,  107,   55,   10,   39,   62,   40,   46,
 /*   160 */     9,   41,  107,   30,   50,   45,    7,   43,   37,   36,
 /*   170 */    26,    5,   33,   31,   81,
    );
    static public $yy_lookahead = array(
 /*     0 */     1,    2,    3,    4,    5,    6,    7,    8,    9,   10,
 /*    10 */    11,   12,   13,   14,   15,    1,    2,    3,    4,    5,
 /*    20 */     6,    7,    8,    9,   10,   11,   12,   13,   14,   15,
 /*    30 */    12,   13,   14,   15,   52,   36,   54,   23,   43,    1,
 /*    40 */     2,    3,    4,    5,    6,    7,    8,    9,   10,   11,
 /*    50 */    12,   13,   14,   15,    2,    3,    4,    5,    6,    7,
 /*    60 */     8,    9,   10,   11,   12,   13,   14,   15,    3,    4,
 /*    70 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*    80 */    15,    3,   41,   42,   30,   44,   24,   25,   14,   15,
 /*    90 */    12,   22,   10,   11,   22,   17,   54,   52,   19,   54,
 /*   100 */    58,   34,   24,   25,   53,   36,   57,   24,   36,   29,
 /*   110 */    35,   33,   20,   35,   29,   37,   38,   39,   22,   33,
 /*   120 */    36,   33,   22,   18,   35,   35,   29,   54,   59,   59,
 /*   130 */    49,   54,   56,   59,   59,   59,   59,   26,   28,   59,
 /*   140 */    21,   54,   59,   27,   59,   54,   47,   59,   54,   56,
 /*   150 */    55,   59,   31,   59,   56,   54,   54,   32,   54,   56,
 /*   160 */    54,   54,   59,   45,   56,   56,   54,   54,   54,   54,
 /*   170 */    50,   54,   48,   46,   51,
);
    const YY_SHIFT_USE_DFLT = -2;
    const YY_SHIFT_MAX = 63;
    static public $yy_shift_ofst = array(
 /*     0 */    79,   78,   78,   78,   14,   38,   38,   38,   38,   38,
 /*    10 */    38,   78,   78,   78,   78,   78,   78,   78,   78,   78,
 /*    20 */    78,   78,   78,   78,   78,   78,  125,  119,  121,  110,
 /*    30 */   111,  116,  105,   54,   67,   -2,   -1,   38,   38,   52,
 /*    40 */    65,   18,   18,   74,   82,   69,   72,   62,   97,  100,
 /*    50 */    96,   89,   80,   96,   90,   96,   88,   92,   75,   83,
 /*    60 */    84,   96,   86,   85,
);
    const YY_REDUCE_USE_DFLT = -19;
    const YY_REDUCE_MAX = 35;
    static public $yy_reduce_ofst = array(
 /*     0 */    41,   42,   45,  -18,   95,   93,   76,  109,  108,  103,
 /*    10 */    98,  106,  104,  102,  107,  113,  112,  101,   87,   77,
 /*    20 */    73,   91,   94,  115,  114,  117,  123,  118,  120,  124,
 /*    30 */   127,   99,   -5,   81,   49,   51,
);
    static public $yyExpectedTokens = array(
        /* 0 */ array(19, ),
        /* 1 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 2 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 3 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 4 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 23, ),
        /* 5 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
        /* 6 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
        /* 7 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
        /* 8 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
        /* 9 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
        /* 10 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
        /* 11 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 12 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 13 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 14 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 15 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 16 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 17 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 18 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 19 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 20 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 21 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 22 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 23 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 24 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 25 */ array(3, 12, 17, 24, 25, 33, 35, 37, 38, 39, ),
        /* 26 */ array(32, ),
        /* 27 */ array(21, ),
        /* 28 */ array(31, ),
        /* 29 */ array(28, ),
        /* 30 */ array(26, ),
        /* 31 */ array(27, ),
        /* 32 */ array(18, ),
        /* 33 */ array(30, ),
        /* 34 */ array(34, ),
        /* 35 */ array(),
        /* 36 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 36, ),
        /* 37 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
        /* 38 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
        /* 39 */ array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
        /* 40 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
        /* 41 */ array(12, 13, 14, 15, ),
        /* 42 */ array(12, 13, 14, 15, ),
        /* 43 */ array(14, 15, ),
        /* 44 */ array(10, 11, ),
        /* 45 */ array(22, 36, ),
        /* 46 */ array(22, 36, ),
        /* 47 */ array(24, 25, ),
        /* 48 */ array(29, ),
        /* 49 */ array(22, ),
        /* 50 */ array(22, ),
        /* 51 */ array(35, ),
        /* 52 */ array(29, ),
        /* 53 */ array(22, ),
        /* 54 */ array(35, ),
        /* 55 */ array(22, ),
        /* 56 */ array(33, ),
        /* 57 */ array(20, ),
        /* 58 */ array(35, ),
        /* 59 */ array(24, ),
        /* 60 */ array(36, ),
        /* 61 */ array(22, ),
        /* 62 */ array(33, ),
        /* 63 */ array(29, ),
        /* 64 */ array(),
        /* 65 */ array(),
        /* 66 */ array(),
        /* 67 */ array(),
        /* 68 */ array(),
        /* 69 */ array(),
        /* 70 */ array(),
        /* 71 */ array(),
        /* 72 */ array(),
        /* 73 */ array(),
        /* 74 */ array(),
        /* 75 */ array(),
        /* 76 */ array(),
        /* 77 */ array(),
        /* 78 */ array(),
        /* 79 */ array(),
        /* 80 */ array(),
        /* 81 */ array(),
        /* 82 */ array(),
        /* 83 */ array(),
        /* 84 */ array(),
        /* 85 */ array(),
);
    static public $yy_default = array(
 /*     0 */   132,  129,  132,  132,   97,  131,  131,  131,  131,  131,
 /*    10 */   131,  132,  132,  132,  132,  132,  132,  132,  132,  132,
 /*    20 */   132,  132,  132,  132,  132,  132,  108,  132,  106,  102,
 /*    30 */   132,  100,   88,  104,  110,   93,  132,  130,   99,  112,
 /*    40 */   115,  114,  111,  113,  132,  132,  132,  132,  132,   91,
 /*    50 */   103,  132,  132,  128,  132,  101,  132,  132,  123,  132,
 /*    60 */   132,  105,  132,  132,   87,   96,   90,   95,   94,   92,
 /*    70 */    86,   98,  125,  107,  119,  118,  116,  109,  120,  121,
 /*    80 */   127,   89,  126,  124,  122,  117,
);
/* The next thing included is series of defines which control
** various aspects of the generated parser.
**    self::YYNOCODE      is a number which corresponds
**                        to no legal terminal or nonterminal number.  This
**                        number is used to fill in empty slots of the hash 
**                        table.
**    self::YYFALLBACK    If defined, this indicates that one or more tokens
**                        have fall-back values which should be used if the
**                        original value of the token will not parse.
**    self::YYSTACKDEPTH  is the maximum depth of the parser's stack.
**    self::YYNSTATE      the combined number of states.
**    self::YYNRULE       the number of rules in the grammar
**    self::YYERRORSYMBOL is the code number of the error symbol.  If not
**                        defined, then do no error processing.
*/
    const YYNOCODE = 60;
    const YYSTACKDEPTH = 100;
    const YYNSTATE = 86;
    const YYNRULE = 46;
    const YYERRORSYMBOL = 40;
    const YYERRSYMDT = 'yy0';
    const YYFALLBACK = 0;
    /** The next table maps tokens into fallback tokens.  If a construct
     * like the following:
     * 
     *      %fallback ID X Y Z.
     *
     * appears in the grammer, then ID becomes a fallback token for X, Y,
     * and Z.  Whenever one of the tokens X, Y, or Z is input to the parser
     * but it does not parse, the type of the token is changed to ID and
     * the parse is retried before an error is thrown.
     */
    static public $yyFallback = array(
    );
    /**
     * Turn parser tracing on by giving a stream to which to write the trace
     * and a prompt to preface each trace message.  Tracing is turned off
     * by making either argument NULL 
     *
     * Inputs:
     * 
     * - A stream resource to which trace output should be written.
     *   If NULL, then tracing is turned off.
     * - A prefix string written at the beginning of every
     *   line of trace output.  If NULL, then tracing is
     *   turned off.
     *
     * Outputs:
     * 
     * - None.
     * @param resource
     * @param string
     */
    static function Trace($TraceFILE, $zTracePrompt)
    {
        if (!$TraceFILE) {
            $zTracePrompt = 0;
        } elseif (!$zTracePrompt) {
            $TraceFILE = 0;
        }
        self::$yyTraceFILE = $TraceFILE;
        self::$yyTracePrompt = $zTracePrompt;
    }

    /**
     * Output debug information to output (php://output stream)
     */
    static function PrintTrace()
    {
        self::$yyTraceFILE = fopen('php://output', 'w');
        self::$yyTracePrompt = '';
    }

    /**
     * @var resource|0
     */
    static public $yyTraceFILE;
    /**
     * String to prepend to debug output
     * @var string|0
     */
    static public $yyTracePrompt;
    /**
     * @var int
     */
    public $yyidx = -1;                    /* Index of top element in stack */
    /**
     * @var int
     */
    public $yyerrcnt;                 /* Shifts left before out of the error */
    /**
     * @var array
     */
    public $yystack = array();  /* The parser's stack */

    /**
     * For tracing shifts, the names of all terminals and nonterminals
     * are required.  The following table supplies these names
     * @var array
     */
    static public $yyTokenName = array( 
  '$',             'OP_OR',         'OP_AND',        'OP_NOT',      
  'OP_EQ',         'OP_NE',         'OP_GT',         'OP_GTEQ',     
  'OP_LT',         'OP_LTEQ',       'OP_IN',         'OP_LIKE',     
  'OP_MINUS',      'OP_PLUS',       'OP_MULTIPLY',   'OP_DIVIDE',   
  'OP_U_MINUS',    'OP_BANG',       'SEMICOLON',     'DISPLAY',     
  'TABLE',         'SELECT',        'COMMA',         'AS',          
  'LITERAL',       'QUOTED',        'FROM',          'WHERE',       
  'SPLIT',         'BY',            'GROUP',         'ORDER',       
  'LIMIT',         'NUMBER',        'OFFSET',        'LEFT_PAREN',  
  'RIGHT_PAREN',   'COLUMN',        'PLACEHOLDER',   'NULL',        
  'error',         'start',         'display_query',  'trailing_semicolon',
  'display_clause',  'select_clause',  'from_clause',   'where_clause',
  'split_clause',  'group_clause',  'order_clause',  'limit_clause',
  'select_field',  'select_fields_extra',  'expression',    'alias_optional',
  'comma_expressions_opt',  'limit_offset_opt',  'func_args',   
    );

    /**
     * For tracing reduce actions, the names of all rules are required.
     * @var array
     */
    static public $yyRuleName = array(
 /*   0 */ "start ::= display_query trailing_semicolon",
 /*   1 */ "trailing_semicolon ::= SEMICOLON",
 /*   2 */ "trailing_semicolon ::=",
 /*   3 */ "display_query ::= display_clause select_clause from_clause where_clause split_clause group_clause order_clause limit_clause",
 /*   4 */ "display_clause ::= DISPLAY TABLE",
 /*   5 */ "select_clause ::= SELECT select_field select_fields_extra",
 /*   6 */ "select_fields_extra ::= select_fields_extra COMMA select_field",
 /*   7 */ "select_fields_extra ::=",
 /*   8 */ "select_field ::= expression alias_optional",
 /*   9 */ "alias_optional ::= AS LITERAL",
 /*  10 */ "alias_optional ::= AS QUOTED",
 /*  11 */ "alias_optional ::=",
 /*  12 */ "from_clause ::= FROM LITERAL",
 /*  13 */ "where_clause ::= WHERE expression",
 /*  14 */ "where_clause ::=",
 /*  15 */ "split_clause ::= SPLIT BY expression comma_expressions_opt",
 /*  16 */ "split_clause ::=",
 /*  17 */ "group_clause ::= GROUP BY expression comma_expressions_opt",
 /*  18 */ "group_clause ::=",
 /*  19 */ "order_clause ::= ORDER BY expression comma_expressions_opt",
 /*  20 */ "order_clause ::=",
 /*  21 */ "limit_clause ::= LIMIT NUMBER limit_offset_opt",
 /*  22 */ "limit_clause ::=",
 /*  23 */ "limit_offset_opt ::= OFFSET NUMBER",
 /*  24 */ "limit_offset_opt ::=",
 /*  25 */ "expression ::= expression OP_EQ|OP_NE|OP_GT|OP_GTEQ|OP_LT|OP_LTEQ expression",
 /*  26 */ "expression ::= expression OP_OR|OP_AND expression",
 /*  27 */ "expression ::= expression OP_MINUS|OP_PLUS|OP_MULTIPLY|OP_DIVIDE expression",
 /*  28 */ "expression ::= expression OP_LIKE expression",
 /*  29 */ "expression ::= expression OP_NOT OP_LIKE expression",
 /*  30 */ "expression ::= expression OP_IN LEFT_PAREN expression comma_expressions_opt RIGHT_PAREN",
 /*  31 */ "expression ::= expression OP_NOT OP_IN LEFT_PAREN expression comma_expressions_opt RIGHT_PAREN",
 /*  32 */ "expression ::= OP_MINUS expression",
 /*  33 */ "expression ::= OP_BANG|OP_NOT expression",
 /*  34 */ "expression ::= LEFT_PAREN expression RIGHT_PAREN",
 /*  35 */ "expression ::= LITERAL LEFT_PAREN func_args RIGHT_PAREN",
 /*  36 */ "expression ::= COLUMN",
 /*  37 */ "expression ::= LITERAL",
 /*  38 */ "expression ::= QUOTED",
 /*  39 */ "expression ::= PLACEHOLDER",
 /*  40 */ "expression ::= NUMBER",
 /*  41 */ "expression ::= NULL",
 /*  42 */ "func_args ::= expression comma_expressions_opt",
 /*  43 */ "func_args ::=",
 /*  44 */ "comma_expressions_opt ::= comma_expressions_opt COMMA expression",
 /*  45 */ "comma_expressions_opt ::=",
    );

    /**
     * This function returns the symbolic name associated with a token
     * value.
     * @param int
     * @return string
     */
    function tokenName($tokenType)
    {
        if ($tokenType === 0) {
            return 'End of Input';
        }
        if ($tokenType > 0 && $tokenType < count(self::$yyTokenName)) {
            return self::$yyTokenName[$tokenType];
        } else {
            return "Unknown";
        }
    }

    /**
     * The following function deletes the value associated with a
     * symbol.  The symbol can be either a terminal or nonterminal.
     * @param int the symbol code
     * @param mixed the symbol's value
     */
    static function yy_destructor($yymajor, $yypminor)
    {
        switch ($yymajor) {
        /* Here is inserted the actions which take place when a
        ** terminal or non-terminal is destroyed.  This can happen
        ** when the symbol is popped from the stack during a
        ** reduce or during error processing or when a parser is 
        ** being destroyed before it is finished parsing.
        **
        ** Note: during a reduce, the only symbols destroyed are those
        ** which appear on the RHS of the rule, but which are not used
        ** inside the C code.
        */
            default:  break;   /* If no destructor action specified: do nothing */
        }
    }

    /**
     * Pop the parser's stack once.
     *
     * If there is a destructor routine associated with the token which
     * is popped from the stack, then call it.
     *
     * Return the major token number for the symbol popped.
     * @param ParseyyParser
     * @return int
     */
    function yy_pop_parser_stack()
    {
        if (!count($this->yystack)) {
            return;
        }
        $yytos = array_pop($this->yystack);
        if (self::$yyTraceFILE && $this->yyidx >= 0) {
            fwrite(self::$yyTraceFILE,
                self::$yyTracePrompt . 'Popping ' . self::$yyTokenName[$yytos->major] .
                    "\n");
        }
        $yymajor = $yytos->major;
        self::yy_destructor($yymajor, $yytos->minor);
        $this->yyidx--;
        return $yymajor;
    }

    /**
     * Deallocate and destroy a parser.  Destructors are all called for
     * all stack elements before shutting the parser down.
     */
    function __destruct()
    {
        while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
        if (is_resource(self::$yyTraceFILE)) {
            fclose(self::$yyTraceFILE);
        }
    }

    /**
     * Based on the current state and parser stack, get a list of all
     * possible lookahead tokens
     * @param int
     * @return array
     */
    function yy_get_expected_tokens($token)
    {
        $state = $this->yystack[$this->yyidx]->stateno;
        $expected = self::$yyExpectedTokens[$state];
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return $expected;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return array_unique($expected);
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate])) {
                        $expected += self::$yyExpectedTokens[$nextstate];
                            if (in_array($token,
                                  self::$yyExpectedTokens[$nextstate], true)) {
                            $this->yyidx = $yyidx;
                            $this->yystack = $stack;
                            return array_unique($expected);
                        }
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new ParseyyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return array_unique($expected);
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return $expected;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        return array_unique($expected);
    }

    /**
     * Based on the parser state and current parser stack, determine whether
     * the lookahead token is possible.
     * 
     * The parser will convert the token value to an error token if not.  This
     * catches some unusual edge cases where the parser would fail.
     * @param int
     * @return bool
     */
    function yy_is_expected_token($token)
    {
        if ($token === 0) {
            return true; // 0 is not part of this
        }
        $state = $this->yystack[$this->yyidx]->stateno;
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return true;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return true;
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate]) &&
                          in_array($token, self::$yyExpectedTokens[$nextstate], true)) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        return true;
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new ParseyyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        if (!$token) {
                            // end of input: this is valid
                            return true;
                        }
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return false;
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return true;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        $this->yyidx = $yyidx;
        $this->yystack = $stack;
        return true;
    }

    /**
     * Find the appropriate action for a parser given the terminal
     * look-ahead token iLookAhead.
     *
     * If the look-ahead token is YYNOCODE, then check to see if the action is
     * independent of the look-ahead.  If it is, return the action, otherwise
     * return YY_NO_ACTION.
     * @param int The look-ahead token
     */
    function yy_find_shift_action($iLookAhead)
    {
        $stateno = $this->yystack[$this->yyidx]->stateno;
     
        /* if ($this->yyidx < 0) return self::YY_NO_ACTION;  */
        if (!isset(self::$yy_shift_ofst[$stateno])) {
            // no shift actions
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_shift_ofst[$stateno];
        if ($i === self::YY_SHIFT_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            if (count(self::$yyFallback) && $iLookAhead < count(self::$yyFallback)
                   && ($iFallback = self::$yyFallback[$iLookAhead]) != 0) {
                if (self::$yyTraceFILE) {
                    fwrite(self::$yyTraceFILE, self::$yyTracePrompt . "FALLBACK " .
                        self::$yyTokenName[$iLookAhead] . " => " .
                        self::$yyTokenName[$iFallback] . "\n");
                }
                return $this->yy_find_shift_action($iFallback);
            }
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    /**
     * Find the appropriate action for a parser given the non-terminal
     * look-ahead token $iLookAhead.
     *
     * If the look-ahead token is self::YYNOCODE, then check to see if the action is
     * independent of the look-ahead.  If it is, return the action, otherwise
     * return self::YY_NO_ACTION.
     * @param int Current state number
     * @param int The look-ahead token
     */
    function yy_find_reduce_action($stateno, $iLookAhead)
    {
        /* $stateno = $this->yystack[$this->yyidx]->stateno; */

        if (!isset(self::$yy_reduce_ofst[$stateno])) {
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_reduce_ofst[$stateno];
        if ($i == self::YY_REDUCE_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    /**
     * Perform a shift action.
     * @param int The new state to shift in
     * @param int The major token to shift in
     * @param mixed the minor token to shift in
     */
    function yy_shift($yyNewState, $yyMajor, $yypMinor)
    {
        $this->yyidx++;
        if ($this->yyidx >= self::YYSTACKDEPTH) {
            $this->yyidx--;
            if (self::$yyTraceFILE) {
                fprintf(self::$yyTraceFILE, "%sStack Overflow!\n", self::$yyTracePrompt);
            }
            while ($this->yyidx >= 0) {
                $this->yy_pop_parser_stack();
            }
            /* Here code is inserted which will execute if the parser
            ** stack ever overflows */
            return;
        }
        $yytos = new ParseyyStackEntry;
        $yytos->stateno = $yyNewState;
        $yytos->major = $yyMajor;
        $yytos->minor = $yypMinor;
        array_push($this->yystack, $yytos);
        if (self::$yyTraceFILE && $this->yyidx > 0) {
            fprintf(self::$yyTraceFILE, "%sShift %d\n", self::$yyTracePrompt,
                $yyNewState);
            fprintf(self::$yyTraceFILE, "%sStack:", self::$yyTracePrompt);
            for ($i = 1; $i <= $this->yyidx; $i++) {
                fprintf(self::$yyTraceFILE, " %s",
                    self::$yyTokenName[$this->yystack[$i]->major]);
            }
            fwrite(self::$yyTraceFILE,"\n");
        }
    }

    /**
     * The following table contains information about every rule that
     * is used during the reduce.
     *
     * <pre>
     * array(
     *  array(
     *   int $lhs;         Symbol on the left-hand side of the rule
     *   int $nrhs;     Number of right-hand side symbols in the rule
     *  ),...
     * );
     * </pre>
     */
    static public $yyRuleInfo = array(
  array( 'lhs' => 41, 'rhs' => 2 ),
  array( 'lhs' => 43, 'rhs' => 1 ),
  array( 'lhs' => 43, 'rhs' => 0 ),
  array( 'lhs' => 42, 'rhs' => 8 ),
  array( 'lhs' => 44, 'rhs' => 2 ),
  array( 'lhs' => 45, 'rhs' => 3 ),
  array( 'lhs' => 53, 'rhs' => 3 ),
  array( 'lhs' => 53, 'rhs' => 0 ),
  array( 'lhs' => 52, 'rhs' => 2 ),
  array( 'lhs' => 55, 'rhs' => 2 ),
  array( 'lhs' => 55, 'rhs' => 2 ),
  array( 'lhs' => 55, 'rhs' => 0 ),
  array( 'lhs' => 46, 'rhs' => 2 ),
  array( 'lhs' => 47, 'rhs' => 2 ),
  array( 'lhs' => 47, 'rhs' => 0 ),
  array( 'lhs' => 48, 'rhs' => 4 ),
  array( 'lhs' => 48, 'rhs' => 0 ),
  array( 'lhs' => 49, 'rhs' => 4 ),
  array( 'lhs' => 49, 'rhs' => 0 ),
  array( 'lhs' => 50, 'rhs' => 4 ),
  array( 'lhs' => 50, 'rhs' => 0 ),
  array( 'lhs' => 51, 'rhs' => 3 ),
  array( 'lhs' => 51, 'rhs' => 0 ),
  array( 'lhs' => 57, 'rhs' => 2 ),
  array( 'lhs' => 57, 'rhs' => 0 ),
  array( 'lhs' => 54, 'rhs' => 3 ),
  array( 'lhs' => 54, 'rhs' => 3 ),
  array( 'lhs' => 54, 'rhs' => 3 ),
  array( 'lhs' => 54, 'rhs' => 3 ),
  array( 'lhs' => 54, 'rhs' => 4 ),
  array( 'lhs' => 54, 'rhs' => 6 ),
  array( 'lhs' => 54, 'rhs' => 7 ),
  array( 'lhs' => 54, 'rhs' => 2 ),
  array( 'lhs' => 54, 'rhs' => 2 ),
  array( 'lhs' => 54, 'rhs' => 3 ),
  array( 'lhs' => 54, 'rhs' => 4 ),
  array( 'lhs' => 54, 'rhs' => 1 ),
  array( 'lhs' => 54, 'rhs' => 1 ),
  array( 'lhs' => 54, 'rhs' => 1 ),
  array( 'lhs' => 54, 'rhs' => 1 ),
  array( 'lhs' => 54, 'rhs' => 1 ),
  array( 'lhs' => 54, 'rhs' => 1 ),
  array( 'lhs' => 58, 'rhs' => 2 ),
  array( 'lhs' => 58, 'rhs' => 0 ),
  array( 'lhs' => 56, 'rhs' => 3 ),
  array( 'lhs' => 56, 'rhs' => 0 ),
    );

    /**
     * The following table contains a mapping of reduce action to method name
     * that handles the reduction.
     * 
     * If a rule is not set, it has no handler.
     */
    static public $yyReduceMap = array(
        3 => 3,
        4 => 4,
        5 => 5,
        15 => 5,
        17 => 5,
        19 => 5,
        42 => 5,
        6 => 6,
        44 => 6,
        8 => 8,
        9 => 9,
        12 => 9,
        13 => 9,
        37 => 9,
        10 => 10,
        38 => 10,
        21 => 21,
        23 => 23,
        25 => 25,
        26 => 26,
        27 => 27,
        28 => 28,
        29 => 29,
        30 => 30,
        31 => 31,
        32 => 32,
        33 => 32,
        34 => 34,
        35 => 35,
        36 => 36,
        39 => 39,
        40 => 40,
        41 => 41,
    );
    /* Beginning here are the reduction cases.  A typical example
    ** follows:
    **  #line <lineno> <grammarfile>
    **   function yy_r0($yymsp){ ... }           // User supplied code
    **  #line <lineno> <thisfile>
    */
#line 69 "Parser.y"
    function yy_r3(){
	$res = new Statement\Display($this->yystack[$this->yyidx + -7]->minor, $this->yystack[$this->yyidx + -6]->minor, $this->yystack[$this->yyidx + -5]->minor);

	if ($this->yystack[$this->yyidx + -4]->minor) {
		$res->setWhere($this->yystack[$this->yyidx + -4]->minor);
	}
	if ($this->yystack[$this->yyidx + -3]->minor) {
		$res->setSplitBy($this->yystack[$this->yyidx + -3]->minor);
	}
	if ($this->yystack[$this->yyidx + -2]->minor) {
		$res->setGroupBy($this->yystack[$this->yyidx + -2]->minor);
	}
	if ($this->yystack[$this->yyidx + -1]->minor) {
		$res->setOrderBy($this->yystack[$this->yyidx + -1]->minor);
	}
	if ($this->yystack[$this->yyidx + 0]->minor) {
		$res->setLimitAmount($this->yystack[$this->yyidx + 0]->minor['limit']);
		if (isset($this->yystack[$this->yyidx + 0]->minor['offset'])) {
			$res->setLimitOffset($this->yystack[$this->yyidx + 0]->minor['offset']);
		}
	}

	$this->_result = $res;
    }
#line 1060 "Parser.php"
#line 97 "Parser.y"
    function yy_r4(){
	$this->_retvalue = 'table';
    }
#line 1065 "Parser.php"
#line 104 "Parser.y"
    function yy_r5(){
	$this->_retvalue = array($this->yystack[$this->yyidx + -1]->minor);
	if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue = array_merge($this->_retvalue, $this->yystack[$this->yyidx + 0]->minor);
	}
    }
#line 1073 "Parser.php"
#line 113 "Parser.y"
    function yy_r6(){
	if (!$this->yystack[$this->yyidx + -2]->minor) {
		$this->_retvalue = array();
	} else {
		$this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
	}
	$this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;
    }
#line 1083 "Parser.php"
#line 126 "Parser.y"
    function yy_r8(){
	if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue = new Statement\Part\Alias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
	} else {
		$this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
	}
    }
#line 1092 "Parser.php"
#line 137 "Parser.y"
    function yy_r9(){
	$this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
    }
#line 1097 "Parser.php"
#line 141 "Parser.y"
    function yy_r10(){
	$this->_retvalue = $this->processQuoted($this->yystack[$this->yyidx + 0]->minor);
    }
#line 1102 "Parser.php"
#line 197 "Parser.y"
    function yy_r21(){
	$this->_retvalue = array('limit' => intval($this->yystack[$this->yyidx + -1]->minor));
	if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue['offset'] = $this->yystack[$this->yyidx + 0]->minor;
	}
    }
#line 1110 "Parser.php"
#line 208 "Parser.y"
    function yy_r23(){
	$this->_retvalue = intval($this->yystack[$this->yyidx + 0]->minor);
    }
#line 1115 "Parser.php"
#line 216 "Parser.y"
    function yy_r25(){
	// this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	$this->_retvalue = new Statement\Part\BinaryComparison($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1123 "Parser.php"
#line 224 "Parser.y"
    function yy_r26(){
	// this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	$this->_retvalue = new Statement\Part\BinaryLogical($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1131 "Parser.php"
#line 232 "Parser.y"
    function yy_r27(){
	// this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	$this->_retvalue = new Statement\Part\BinaryMath($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1139 "Parser.php"
#line 240 "Parser.y"
    function yy_r28(){
	$this->_retvalue = new Statement\Part\Like($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1144 "Parser.php"
#line 245 "Parser.y"
    function yy_r29(){
	$this->_retvalue = new Statement\Part\Like($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
#line 1149 "Parser.php"
#line 250 "Parser.y"
    function yy_r30(){
	$values = array($this->yystack[$this->yyidx + -2]->minor);
	if ($this->yystack[$this->yyidx + -1]->minor) {
		$values = array_merge($values, $this->yystack[$this->yyidx + -1]->minor);
	}
	$this->_retvalue = new Statement\Part\In($this->yystack[$this->yyidx + -5]->minor, $values);
    }
#line 1158 "Parser.php"
#line 259 "Parser.y"
    function yy_r31(){
	$values = array($this->yystack[$this->yyidx + -2]->minor);
	if ($this->yystack[$this->yyidx + -1]->minor) {
		$values = array_merge($values, $this->yystack[$this->yyidx + -1]->minor);
	}
	$this->_retvalue = new Statement\Part\In($this->yystack[$this->yyidx + -6]->minor, $values);
    }
#line 1167 "Parser.php"
#line 268 "Parser.y"
    function yy_r32(){
	// this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	$this->_retvalue = new Statement\Part\UnaryOperator($token, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1175 "Parser.php"
#line 284 "Parser.y"
    function yy_r34(){
	$this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
    }
#line 1180 "Parser.php"
#line 289 "Parser.y"
    function yy_r35(){
	if (!$this->yystack[$this->yyidx + -1]->minor) {
		$this->yystack[$this->yyidx + -1]->minor = array();
	}
	$this->_retvalue = new Statement\Part\FunctionCall($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + -1]->minor);
    }
#line 1188 "Parser.php"
#line 297 "Parser.y"
    function yy_r36(){
	$parts = explode('.', $this->yystack[$this->yyidx + 0]->minor, 2);
	$this->_retvalue = new Statement\Part\Column($parts[0], $parts[1]);
    }
#line 1194 "Parser.php"
#line 313 "Parser.y"
    function yy_r39(){
	$value = substr($this->yystack[$this->yyidx + 0]->minor, 1, -1);
	$this->_retvalue = new Statement\Part\Placeholder($value);
    }
#line 1200 "Parser.php"
#line 319 "Parser.y"
    function yy_r40(){
	$this->_retvalue = $this->yystack[$this->yyidx + 0]->minor + 0;
    }
#line 1205 "Parser.php"
#line 324 "Parser.y"
    function yy_r41(){
	$this->_retvalue = new Statement\Part\NullValue();
    }
#line 1210 "Parser.php"

    /**
     * placeholder for the left hand side in a reduce operation.
     * 
     * For a parser with a rule like this:
     * <pre>
     * rule(A) ::= B. { A = 1; }
     * </pre>
     * 
     * The parser will translate to something like:
     * 
     * <code>
     * function yy_r0(){$this->_retvalue = 1;}
     * </code>
     */
    private $_retvalue;

    /**
     * Perform a reduce action and the shift that must immediately
     * follow the reduce.
     * 
     * For a rule such as:
     * 
     * <pre>
     * A ::= B blah C. { dosomething(); }
     * </pre>
     * 
     * This function will first call the action, if any, ("dosomething();" in our
     * example), and then it will pop three states from the stack,
     * one for each entry on the right-hand side of the expression
     * (B, blah, and C in our example rule), and then push the result of the action
     * back on to the stack with the resulting state reduced to (as described in the .out
     * file)
     * @param int Number of the rule by which to reduce
     */
    function yy_reduce($yyruleno)
    {
        //int $yygoto;                     /* The next state */
        //int $yyact;                      /* The next action */
        //mixed $yygotominor;        /* The LHS of the rule reduced */
        //ParseyyStackEntry $yymsp;            /* The top of the parser's stack */
        //int $yysize;                     /* Amount to pop the stack */
        $yymsp = $this->yystack[$this->yyidx];
        if (self::$yyTraceFILE && $yyruleno >= 0 
              && $yyruleno < count(self::$yyRuleName)) {
            fprintf(self::$yyTraceFILE, "%sReduce (%d) [%s].\n",
                self::$yyTracePrompt, $yyruleno,
                self::$yyRuleName[$yyruleno]);
        }

        $this->_retvalue = $yy_lefthand_side = null;
        if (array_key_exists($yyruleno, self::$yyReduceMap)) {
            // call the action
            $this->_retvalue = null;
            $this->{'yy_r' . self::$yyReduceMap[$yyruleno]}();
            $yy_lefthand_side = $this->_retvalue;
        }
        $yygoto = self::$yyRuleInfo[$yyruleno]['lhs'];
        $yysize = self::$yyRuleInfo[$yyruleno]['rhs'];
        $this->yyidx -= $yysize;
        for ($i = $yysize; $i; $i--) {
            // pop all of the right-hand side parameters
            array_pop($this->yystack);
        }
        $yyact = $this->yy_find_reduce_action($this->yystack[$this->yyidx]->stateno, $yygoto);
        if ($yyact < self::YYNSTATE) {
            /* If we are not debugging and the reduce action popped at least
            ** one element off the stack, then we can push the new element back
            ** onto the stack here, and skip the stack overflow test in yy_shift().
            ** That gives a significant speed improvement. */
            if (!self::$yyTraceFILE && $yysize) {
                $this->yyidx++;
                $x = new ParseyyStackEntry;
                $x->stateno = $yyact;
                $x->major = $yygoto;
                $x->minor = $yy_lefthand_side;
                $this->yystack[$this->yyidx] = $x;
            } else {
                $this->yy_shift($yyact, $yygoto, $yy_lefthand_side);
            }
        } elseif ($yyact == self::YYNSTATE + self::YYNRULE + 1) {
            $this->yy_accept();
        }
    }

    /**
     * The following code executes when the parse fails
     * 
     * Code from %parse_fail is inserted here
     */
    function yy_parse_failed()
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sFail!\n", self::$yyTracePrompt);
        }
        while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
        /* Here code is inserted which will be executed whenever the
        ** parser fails */
    }

    /**
     * The following code executes when a syntax error first occurs.
     * 
     * %syntax_error code is inserted here
     * @param int The major type of the error token
     * @param mixed The minor type of the error token
     */
    function yy_syntax_error($yymajor, $TOKEN)
    {
#line 5 "Parser.y"

	throw new \Exception('Parsing error');
#line 1326 "Parser.php"
    }

    /**
     * The following is executed when the parser accepts
     * 
     * %parse_accept code is inserted here
     */
    function yy_accept()
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sAccept!\n", self::$yyTracePrompt);
        }
        while ($this->yyidx >= 0) {
            $stack = $this->yy_pop_parser_stack();
        }
        /* Here code is inserted which will be executed whenever the
        ** parser accepts */
    }

    /**
     * The main parser program.
     * 
     * The first argument is the major token number.  The second is
     * the token value string as scanned from the input.
     *
     * @param int   $yymajor      the token number
     * @param mixed $yytokenvalue the token value
     * @param mixed ...           any extra arguments that should be passed to handlers
     *
     * @return void
     */
    function doParse($yymajor, $yytokenvalue)
    {
//        $yyact;            /* The parser action. */
//        $yyendofinput;     /* True if we are at the end of input */
        $yyerrorhit = 0;   /* True if yymajor has invoked an error */
        
        /* (re)initialize the parser, if necessary */
        if ($this->yyidx === null || $this->yyidx < 0) {
            /* if ($yymajor == 0) return; // not sure why this was here... */
            $this->yyidx = 0;
            $this->yyerrcnt = -1;
            $x = new ParseyyStackEntry;
            $x->stateno = 0;
            $x->major = 0;
            $this->yystack = array();
            array_push($this->yystack, $x);
        }
        $yyendofinput = ($yymajor==0);
        
        if (self::$yyTraceFILE) {
            fprintf(
                self::$yyTraceFILE,
                "%sInput %s\n",
                self::$yyTracePrompt,
                self::$yyTokenName[$yymajor]
            );
        }
        
        do {
            $yyact = $this->yy_find_shift_action($yymajor);
            if ($yymajor < self::YYERRORSYMBOL
                && !$this->yy_is_expected_token($yymajor)
            ) {
                // force a syntax error
                $yyact = self::YY_ERROR_ACTION;
            }
            if ($yyact < self::YYNSTATE) {
                $this->yy_shift($yyact, $yymajor, $yytokenvalue);
                $this->yyerrcnt--;
                if ($yyendofinput && $this->yyidx >= 0) {
                    $yymajor = 0;
                } else {
                    $yymajor = self::YYNOCODE;
                }
            } elseif ($yyact < self::YYNSTATE + self::YYNRULE) {
                $this->yy_reduce($yyact - self::YYNSTATE);
            } elseif ($yyact == self::YY_ERROR_ACTION) {
                if (self::$yyTraceFILE) {
                    fprintf(
                        self::$yyTraceFILE,
                        "%sSyntax Error!\n",
                        self::$yyTracePrompt
                    );
                }
                if (self::YYERRORSYMBOL) {
                    /* A syntax error has occurred.
                    ** The response to an error depends upon whether or not the
                    ** grammar defines an error token "ERROR".  
                    **
                    ** This is what we do if the grammar does define ERROR:
                    **
                    **  * Call the %syntax_error function.
                    **
                    **  * Begin popping the stack until we enter a state where
                    **    it is legal to shift the error symbol, then shift
                    **    the error symbol.
                    **
                    **  * Set the error count to three.
                    **
                    **  * Begin accepting and shifting new tokens.  No new error
                    **    processing will occur until three tokens have been
                    **    shifted successfully.
                    **
                    */
                    if ($this->yyerrcnt < 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $yymx = $this->yystack[$this->yyidx]->major;
                    if ($yymx == self::YYERRORSYMBOL || $yyerrorhit ) {
                        if (self::$yyTraceFILE) {
                            fprintf(
                                self::$yyTraceFILE,
                                "%sDiscard input token %s\n",
                                self::$yyTracePrompt,
                                self::$yyTokenName[$yymajor]
                            );
                        }
                        $this->yy_destructor($yymajor, $yytokenvalue);
                        $yymajor = self::YYNOCODE;
                    } else {
                        while ($this->yyidx >= 0
                            && $yymx != self::YYERRORSYMBOL
                            && ($yyact = $this->yy_find_shift_action(self::YYERRORSYMBOL)) >= self::YYNSTATE
                        ) {
                            $this->yy_pop_parser_stack();
                        }
                        if ($this->yyidx < 0 || $yymajor==0) {
                            $this->yy_destructor($yymajor, $yytokenvalue);
                            $this->yy_parse_failed();
                            $yymajor = self::YYNOCODE;
                        } elseif ($yymx != self::YYERRORSYMBOL) {
                            $u2 = 0;
                            $this->yy_shift($yyact, self::YYERRORSYMBOL, $u2);
                        }
                    }
                    $this->yyerrcnt = 3;
                    $yyerrorhit = 1;
                } else {
                    /* YYERRORSYMBOL is not defined */
                    /* This is what we do if the grammar does not define ERROR:
                    **
                    **  * Report an error message, and throw away the input token.
                    **
                    **  * If the input token is $, then fail the parse.
                    **
                    ** As before, subsequent error messages are suppressed until
                    ** three input tokens have been successfully shifted.
                    */
                    if ($this->yyerrcnt <= 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $this->yyerrcnt = 3;
                    $this->yy_destructor($yymajor, $yytokenvalue);
                    if ($yyendofinput) {
                        $this->yy_parse_failed();
                    }
                    $yymajor = self::YYNOCODE;
                }
            } else {
                $this->yy_accept();
                $yymajor = self::YYNOCODE;
            }            
        } while ($yymajor != self::YYNOCODE && $this->yyidx >= 0);
    }
}
