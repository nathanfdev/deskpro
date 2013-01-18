<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
		\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Dpql
 */

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

	/**
	 * Line number currently being parsed. This comes from the lexer.
	 *
	 * @var integer
	 */
	public $line = 1;

	/**
	 * The output of parsing. When parsing has run, this will be a statement object.
	 *
	 * @var \Application\DeskPRO\Dpql\Statement\Display|null
	 */
	protected $_result = null;

	/**
	 * Gets the result object.
	 *
	 * @return \Application\DeskPRO\Dpql\Statement\Display|null
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
#line 167 "Parser.php"

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
    const T_OP_REGEXP                      = 12;
    const T_OP_MINUS                       = 13;
    const T_OP_PLUS                        = 14;
    const T_OP_MULTIPLY                    = 15;
    const T_OP_DIVIDE                      = 16;
    const T_OP_U_MINUS                     = 17;
    const T_OP_BANG                        = 18;
    const T_SEMICOLON                      = 19;
    const T_DISPLAY                        = 20;
    const T_TABLE                          = 21;
    const T_BAR                            = 22;
    const T_LINE                           = 23;
    const T_PIE                            = 24;
    const T_COMMA                          = 25;
    const T_SELECT                         = 26;
    const T_AS                             = 27;
    const T_LITERAL                        = 28;
    const T_QUOTED                         = 29;
    const T_FROM                           = 30;
    const T_WHERE                          = 31;
    const T_SPLIT                          = 32;
    const T_BY                             = 33;
    const T_GROUP                          = 34;
    const T_ORDER                          = 35;
    const T_ASC                            = 36;
    const T_DESC                           = 37;
    const T_LIMIT                          = 38;
    const T_NUMBER                         = 39;
    const T_OFFSET                         = 40;
    const T_INTERVAL                       = 41;
    const T_LEFT_PAREN                     = 42;
    const T_RIGHT_PAREN                    = 43;
    const T_COLUMN                         = 44;
    const T_PLACEHOLDER                    = 45;
    const T_AT                             = 46;
    const T_NULL                           = 47;
    const YY_NO_ACTION = 192;
    const YY_ACCEPT_ACTION = 191;
    const YY_ERROR_ACTION = 190;

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
    const YY_SZ_ACTTAB = 232;
static public $yy_action = array(
 /*     0 */    26,   26,   58,   29,   29,   29,   29,   29,   29,   79,
 /*    10 */    19,   17,    1,    1,   20,   20,   26,   26,   58,   29,
 /*    20 */    29,   29,   29,   29,   29,   79,   19,   17,    1,    1,
 /*    30 */    20,   20,   72,   18,   30,   99,  112,   94,   93,   90,
 /*    40 */    91,   51,   62,  111,   26,   26,   58,   29,   29,   29,
 /*    50 */    29,   29,   29,   79,   19,   17,    1,    1,   20,   20,
 /*    60 */    26,   26,   58,   29,   29,   29,   29,   29,   29,   79,
 /*    70 */    19,   17,    1,    1,   20,   20,    1,    1,   20,   20,
 /*    80 */   191,   39,   11,   38,  108,  109,  102,   35,  118,   26,
 /*    90 */    58,   29,   29,   29,   29,   29,   29,   79,   19,   17,
 /*   100 */     1,    1,   20,   20,   58,   29,   29,   29,   29,   29,
 /*   110 */    29,   79,   19,   17,    1,    1,   20,   20,   24,   22,
 /*   120 */   101,  113,   22,   98,   15,   13,   13,  103,   25,  100,
 /*   130 */    43,   48,   81,   24,   12,   55,   71,   87,   88,   11,
 /*   140 */   114,   85,   50,   73,  106,   44,    4,   45,   24,   12,
 /*   150 */    51,  104,   46,  115,   83,   86,   65,   23,   25,  107,
 /*   160 */   105,   60,   82,   24,   78,   68,   27,  116,   59,    9,
 /*   170 */   110,   32,    6,   73,  106,   57,   49,  117,   47,   22,
 /*   180 */    52,   16,   84,    7,   83,   10,   14,   23,   21,  107,
 /*   190 */   105,   60,   82,   63,   33,    8,   64,   77,   80,   96,
 /*   200 */    31,   28,   40,   61,    3,   53,   54,   76,   66,   92,
 /*   210 */    36,    5,   56,   41,   89,   75,   34,    2,   95,   37,
 /*   220 */    42,   67,   97,   70,  150,  150,  150,  150,   69,  150,
 /*   230 */   150,   74,
    );
    static public $yy_lookahead = array(
 /*     0 */     1,    2,    3,    4,    5,    6,    7,    8,    9,   10,
 /*    10 */    11,   12,   13,   14,   15,   16,    1,    2,    3,    4,
 /*    20 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*    30 */    15,   16,   10,   11,   12,   36,   37,   21,   22,   23,
 /*    40 */    24,   64,   27,   66,    1,    2,    3,    4,    5,    6,
 /*    50 */     7,    8,    9,   10,   11,   12,   13,   14,   15,   16,
 /*    60 */     1,    2,    3,    4,    5,    6,    7,    8,    9,   10,
 /*    70 */    11,   12,   13,   14,   15,   16,   13,   14,   15,   16,
 /*    80 */    49,   50,   64,   52,   28,   29,   43,   39,   70,    2,
 /*    90 */     3,    4,    5,    6,    7,    8,    9,   10,   11,   12,
 /*   100 */    13,   14,   15,   16,    3,    4,    5,    6,    7,    8,
 /*   110 */     9,   10,   11,   12,   13,   14,   15,   16,    3,   25,
 /*   120 */    28,   29,   25,   72,   64,   64,   64,   28,   13,   68,
 /*   130 */    68,   64,   62,   18,   64,   64,   76,   43,   65,   64,
 /*   140 */    43,   74,   64,   28,   29,   70,   25,   62,    3,   64,
 /*   150 */    64,   43,   66,   65,   39,   28,   41,   42,   13,   44,
 /*   160 */    45,   46,   47,   18,   75,   67,   42,   64,   75,   25,
 /*   170 */    64,   20,   33,   28,   29,   64,   64,   64,   64,   25,
 /*   180 */    64,   64,   39,   25,   39,   33,   64,   42,   42,   44,
 /*   190 */    45,   46,   47,   63,   54,   42,   30,   40,   73,   61,
 /*   200 */    25,   31,   55,   75,   25,   64,   64,   38,   34,   60,
 /*   210 */    60,   33,   64,   57,   59,   35,   53,   26,   19,   58,
 /*   220 */    56,   32,   51,   39,   77,   77,   77,   77,   69,   77,
 /*   230 */    77,   71,
);
    const YY_SHIFT_USE_DFLT = -2;
    const YY_SHIFT_MAX = 79;
    static public $yy_shift_ofst = array(
 /*     0 */   151,  115,  145,  145,  145,  145,  145,  145,  145,  145,
 /*    10 */   145,   -1,   15,   15,   59,   59,   59,  145,  145,  145,
 /*    20 */   145,  145,  145,  145,  145,  145,  145,  145,  145,  145,
 /*    30 */   145,   16,   16,  170,  166,  157,  175,  169,  191,  199,
 /*    40 */   189,  180,  174,   -2,   -2,   -2,   -2,   43,   59,   59,
 /*    50 */    59,   59,   87,  101,  101,   63,   63,   63,   22,   97,
 /*    60 */    92,   94,   56,  121,  127,  184,  139,  152,  158,  144,
 /*    70 */    99,  108,  124,  153,  179,  178,   48,  143,  154,  146,
);
    const YY_REDUCE_USE_DFLT = -24;
    const YY_REDUCE_MAX = 46;
    static public $yy_reduce_ofst = array(
 /*     0 */    31,   67,   85,   18,   70,   75,   62,  -23,   60,   61,
 /*    10 */    86,   51,   73,   88,   93,   89,  128,  148,  142,   71,
 /*    20 */   106,  122,  112,  114,  103,  113,  116,  117,   78,  111,
 /*    30 */   141,  149,  150,  147,  140,  125,  138,  155,  163,  171,
 /*    40 */   164,  161,  156,  159,  160,  130,   98,
);
    static public $yyExpectedTokens = array(
        /* 0 */ array(20, ),
        /* 1 */ array(3, 13, 18, 28, 29, 39, 41, 42, 44, 45, 46, 47, ),
        /* 2 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 3 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 4 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 5 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 6 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 7 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 8 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 9 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 10 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 11 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 36, 37, ),
        /* 12 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 27, ),
        /* 13 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 27, ),
        /* 14 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 15 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 16 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 17 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 18 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 19 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 20 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 21 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 22 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 23 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 24 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 25 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 26 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 27 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 28 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 29 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 30 */ array(3, 13, 18, 28, 29, 39, 42, 44, 45, 46, 47, ),
        /* 31 */ array(21, 22, 23, 24, ),
        /* 32 */ array(21, 22, 23, 24, ),
        /* 33 */ array(31, ),
        /* 34 */ array(30, ),
        /* 35 */ array(40, ),
        /* 36 */ array(25, ),
        /* 37 */ array(38, ),
        /* 38 */ array(26, ),
        /* 39 */ array(19, ),
        /* 40 */ array(32, ),
        /* 41 */ array(35, ),
        /* 42 */ array(34, ),
        /* 43 */ array(),
        /* 44 */ array(),
        /* 45 */ array(),
        /* 46 */ array(),
        /* 47 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 43, ),
        /* 48 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 49 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 50 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 51 */ array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 52 */ array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 53 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 54 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 55 */ array(13, 14, 15, 16, ),
        /* 56 */ array(13, 14, 15, 16, ),
        /* 57 */ array(13, 14, 15, 16, ),
        /* 58 */ array(10, 11, 12, ),
        /* 59 */ array(25, 43, ),
        /* 60 */ array(28, 29, ),
        /* 61 */ array(25, 43, ),
        /* 62 */ array(28, 29, ),
        /* 63 */ array(25, ),
        /* 64 */ array(28, ),
        /* 65 */ array(39, ),
        /* 66 */ array(33, ),
        /* 67 */ array(33, ),
        /* 68 */ array(25, ),
        /* 69 */ array(25, ),
        /* 70 */ array(28, ),
        /* 71 */ array(43, ),
        /* 72 */ array(42, ),
        /* 73 */ array(42, ),
        /* 74 */ array(25, ),
        /* 75 */ array(33, ),
        /* 76 */ array(39, ),
        /* 77 */ array(39, ),
        /* 78 */ array(25, ),
        /* 79 */ array(42, ),
        /* 80 */ array(),
        /* 81 */ array(),
        /* 82 */ array(),
        /* 83 */ array(),
        /* 84 */ array(),
        /* 85 */ array(),
        /* 86 */ array(),
        /* 87 */ array(),
        /* 88 */ array(),
        /* 89 */ array(),
        /* 90 */ array(),
        /* 91 */ array(),
        /* 92 */ array(),
        /* 93 */ array(),
        /* 94 */ array(),
        /* 95 */ array(),
        /* 96 */ array(),
        /* 97 */ array(),
        /* 98 */ array(),
        /* 99 */ array(),
        /* 100 */ array(),
        /* 101 */ array(),
        /* 102 */ array(),
        /* 103 */ array(),
        /* 104 */ array(),
        /* 105 */ array(),
        /* 106 */ array(),
        /* 107 */ array(),
        /* 108 */ array(),
        /* 109 */ array(),
        /* 110 */ array(),
        /* 111 */ array(),
        /* 112 */ array(),
        /* 113 */ array(),
        /* 114 */ array(),
        /* 115 */ array(),
        /* 116 */ array(),
        /* 117 */ array(),
        /* 118 */ array(),
);
    static public $yy_default = array(
 /*     0 */   190,  190,  190,  190,  190,  190,  190,  190,  187,  190,
 /*    10 */   190,  155,  136,  136,  189,  189,  189,  190,  190,  190,
 /*    20 */   190,  190,  190,  190,  190,  190,  190,  190,  190,  190,
 /*    30 */   190,  190,  190,  139,  190,  161,  129,  159,  190,  121,
 /*    40 */   141,  151,  146,  148,  157,  132,  143,  190,  166,  188,
 /*    50 */   138,  144,  163,  171,  169,  168,  170,  162,  190,  190,
 /*    60 */   190,  190,  190,  130,  190,  190,  190,  190,  140,  145,
 /*    70 */   190,  190,  190,  179,  150,  190,  190,  190,  186,  190,
 /*    80 */   158,  131,  185,  184,  160,  164,  137,  173,  133,  122,
 /*    90 */   126,  127,  128,  125,  124,  120,  123,  119,  152,  153,
 /*   100 */   147,  182,  176,  165,  177,  181,  180,  178,  134,  135,
 /*   110 */   167,  142,  154,  183,  172,  149,  175,  174,  156,
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
    const YYNOCODE = 78;
    const YYSTACKDEPTH = 100;
    const YYNSTATE = 119;
    const YYNRULE = 71;
    const YYERRORSYMBOL = 48;
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
  'OP_REGEXP',     'OP_MINUS',      'OP_PLUS',       'OP_MULTIPLY', 
  'OP_DIVIDE',     'OP_U_MINUS',    'OP_BANG',       'SEMICOLON',   
  'DISPLAY',       'TABLE',         'BAR',           'LINE',        
  'PIE',           'COMMA',         'SELECT',        'AS',          
  'LITERAL',       'QUOTED',        'FROM',          'WHERE',       
  'SPLIT',         'BY',            'GROUP',         'ORDER',       
  'ASC',           'DESC',          'LIMIT',         'NUMBER',      
  'OFFSET',        'INTERVAL',      'LEFT_PAREN',    'RIGHT_PAREN', 
  'COLUMN',        'PLACEHOLDER',   'AT',            'NULL',        
  'error',         'start',         'display_query',  'trailing_semicolon',
  'display_clause',  'select_clause',  'from_clause',   'where_clause',
  'split_clause',  'group_clause',  'order_clause',  'limit_clause',
  'display_type',  'display_type_option',  'select_field',  'select_fields_extra',
  'expression',    'alias_optional',  'split_expression',  'split_expressions_extra',
  'group_expression',  'group_expressions_extra',  'order_expression',  'comma_order_expression_opt',
  'direction_opt',  'limit_offset_opt',  'interval_expression',  'comma_expressions_opt',
  'func_args',   
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
 /*   4 */ "display_clause ::= DISPLAY display_type display_type_option",
 /*   5 */ "display_type ::= TABLE",
 /*   6 */ "display_type ::= BAR",
 /*   7 */ "display_type ::= LINE",
 /*   8 */ "display_type ::= PIE",
 /*   9 */ "display_type_option ::= COMMA display_type",
 /*  10 */ "display_type_option ::=",
 /*  11 */ "select_clause ::= SELECT select_field select_fields_extra",
 /*  12 */ "select_fields_extra ::= select_fields_extra COMMA select_field",
 /*  13 */ "select_fields_extra ::=",
 /*  14 */ "select_field ::= expression alias_optional",
 /*  15 */ "alias_optional ::= AS LITERAL",
 /*  16 */ "alias_optional ::= AS QUOTED",
 /*  17 */ "alias_optional ::=",
 /*  18 */ "from_clause ::= FROM LITERAL",
 /*  19 */ "where_clause ::= WHERE expression",
 /*  20 */ "where_clause ::=",
 /*  21 */ "split_clause ::= SPLIT BY split_expression split_expressions_extra",
 /*  22 */ "split_clause ::=",
 /*  23 */ "split_expressions_extra ::= split_expressions_extra COMMA split_expression",
 /*  24 */ "split_expressions_extra ::=",
 /*  25 */ "split_expression ::= expression",
 /*  26 */ "group_clause ::= GROUP BY group_expression group_expressions_extra",
 /*  27 */ "group_clause ::=",
 /*  28 */ "group_expressions_extra ::= group_expressions_extra COMMA group_expression",
 /*  29 */ "group_expressions_extra ::=",
 /*  30 */ "group_expression ::= expression alias_optional",
 /*  31 */ "order_clause ::= ORDER BY order_expression comma_order_expression_opt",
 /*  32 */ "order_clause ::=",
 /*  33 */ "order_expression ::= expression direction_opt",
 /*  34 */ "direction_opt ::= ASC",
 /*  35 */ "direction_opt ::= DESC",
 /*  36 */ "direction_opt ::=",
 /*  37 */ "comma_order_expression_opt ::= comma_order_expression_opt COMMA order_expression",
 /*  38 */ "comma_order_expression_opt ::=",
 /*  39 */ "limit_clause ::= LIMIT NUMBER limit_offset_opt",
 /*  40 */ "limit_clause ::=",
 /*  41 */ "limit_offset_opt ::= OFFSET NUMBER",
 /*  42 */ "limit_offset_opt ::=",
 /*  43 */ "expression ::= expression OP_EQ|OP_NE|OP_GT|OP_GTEQ|OP_LT|OP_LTEQ expression",
 /*  44 */ "expression ::= expression OP_OR|OP_AND expression",
 /*  45 */ "expression ::= expression OP_MINUS|OP_PLUS interval_expression",
 /*  46 */ "interval_expression ::= INTERVAL NUMBER LITERAL",
 /*  47 */ "interval_expression ::= expression",
 /*  48 */ "expression ::= expression OP_MULTIPLY|OP_DIVIDE expression",
 /*  49 */ "expression ::= expression OP_LIKE expression",
 /*  50 */ "expression ::= expression OP_NOT OP_LIKE expression",
 /*  51 */ "expression ::= expression OP_REGEXP expression",
 /*  52 */ "expression ::= expression OP_NOT OP_REGEXP expression",
 /*  53 */ "expression ::= expression OP_IN LEFT_PAREN expression comma_expressions_opt RIGHT_PAREN",
 /*  54 */ "expression ::= expression OP_NOT OP_IN LEFT_PAREN expression comma_expressions_opt RIGHT_PAREN",
 /*  55 */ "expression ::= OP_MINUS expression",
 /*  56 */ "expression ::= OP_BANG|OP_NOT expression",
 /*  57 */ "expression ::= LEFT_PAREN expression RIGHT_PAREN",
 /*  58 */ "expression ::= LITERAL LEFT_PAREN func_args RIGHT_PAREN",
 /*  59 */ "expression ::= COLUMN",
 /*  60 */ "expression ::= LITERAL",
 /*  61 */ "expression ::= QUOTED",
 /*  62 */ "expression ::= PLACEHOLDER",
 /*  63 */ "expression ::= AT LITERAL",
 /*  64 */ "expression ::= AT QUOTED",
 /*  65 */ "expression ::= NUMBER",
 /*  66 */ "expression ::= NULL",
 /*  67 */ "func_args ::= expression comma_expressions_opt",
 /*  68 */ "func_args ::=",
 /*  69 */ "comma_expressions_opt ::= comma_expressions_opt COMMA expression",
 /*  70 */ "comma_expressions_opt ::=",
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
  array( 'lhs' => 49, 'rhs' => 2 ),
  array( 'lhs' => 51, 'rhs' => 1 ),
  array( 'lhs' => 51, 'rhs' => 0 ),
  array( 'lhs' => 50, 'rhs' => 8 ),
  array( 'lhs' => 52, 'rhs' => 3 ),
  array( 'lhs' => 60, 'rhs' => 1 ),
  array( 'lhs' => 60, 'rhs' => 1 ),
  array( 'lhs' => 60, 'rhs' => 1 ),
  array( 'lhs' => 60, 'rhs' => 1 ),
  array( 'lhs' => 61, 'rhs' => 2 ),
  array( 'lhs' => 61, 'rhs' => 0 ),
  array( 'lhs' => 53, 'rhs' => 3 ),
  array( 'lhs' => 63, 'rhs' => 3 ),
  array( 'lhs' => 63, 'rhs' => 0 ),
  array( 'lhs' => 62, 'rhs' => 2 ),
  array( 'lhs' => 65, 'rhs' => 2 ),
  array( 'lhs' => 65, 'rhs' => 2 ),
  array( 'lhs' => 65, 'rhs' => 0 ),
  array( 'lhs' => 54, 'rhs' => 2 ),
  array( 'lhs' => 55, 'rhs' => 2 ),
  array( 'lhs' => 55, 'rhs' => 0 ),
  array( 'lhs' => 56, 'rhs' => 4 ),
  array( 'lhs' => 56, 'rhs' => 0 ),
  array( 'lhs' => 67, 'rhs' => 3 ),
  array( 'lhs' => 67, 'rhs' => 0 ),
  array( 'lhs' => 66, 'rhs' => 1 ),
  array( 'lhs' => 57, 'rhs' => 4 ),
  array( 'lhs' => 57, 'rhs' => 0 ),
  array( 'lhs' => 69, 'rhs' => 3 ),
  array( 'lhs' => 69, 'rhs' => 0 ),
  array( 'lhs' => 68, 'rhs' => 2 ),
  array( 'lhs' => 58, 'rhs' => 4 ),
  array( 'lhs' => 58, 'rhs' => 0 ),
  array( 'lhs' => 70, 'rhs' => 2 ),
  array( 'lhs' => 72, 'rhs' => 1 ),
  array( 'lhs' => 72, 'rhs' => 1 ),
  array( 'lhs' => 72, 'rhs' => 0 ),
  array( 'lhs' => 71, 'rhs' => 3 ),
  array( 'lhs' => 71, 'rhs' => 0 ),
  array( 'lhs' => 59, 'rhs' => 3 ),
  array( 'lhs' => 59, 'rhs' => 0 ),
  array( 'lhs' => 73, 'rhs' => 2 ),
  array( 'lhs' => 73, 'rhs' => 0 ),
  array( 'lhs' => 64, 'rhs' => 3 ),
  array( 'lhs' => 64, 'rhs' => 3 ),
  array( 'lhs' => 64, 'rhs' => 3 ),
  array( 'lhs' => 74, 'rhs' => 3 ),
  array( 'lhs' => 74, 'rhs' => 1 ),
  array( 'lhs' => 64, 'rhs' => 3 ),
  array( 'lhs' => 64, 'rhs' => 3 ),
  array( 'lhs' => 64, 'rhs' => 4 ),
  array( 'lhs' => 64, 'rhs' => 3 ),
  array( 'lhs' => 64, 'rhs' => 4 ),
  array( 'lhs' => 64, 'rhs' => 6 ),
  array( 'lhs' => 64, 'rhs' => 7 ),
  array( 'lhs' => 64, 'rhs' => 2 ),
  array( 'lhs' => 64, 'rhs' => 2 ),
  array( 'lhs' => 64, 'rhs' => 3 ),
  array( 'lhs' => 64, 'rhs' => 4 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 64, 'rhs' => 2 ),
  array( 'lhs' => 64, 'rhs' => 2 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 76, 'rhs' => 2 ),
  array( 'lhs' => 76, 'rhs' => 0 ),
  array( 'lhs' => 75, 'rhs' => 3 ),
  array( 'lhs' => 75, 'rhs' => 0 ),
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
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        15 => 9,
        18 => 9,
        19 => 9,
        11 => 11,
        31 => 11,
        67 => 11,
        12 => 12,
        37 => 12,
        69 => 12,
        14 => 14,
        16 => 16,
        21 => 21,
        26 => 21,
        23 => 23,
        28 => 23,
        25 => 25,
        30 => 30,
        33 => 33,
        34 => 34,
        35 => 35,
        39 => 39,
        41 => 41,
        43 => 43,
        44 => 44,
        45 => 45,
        46 => 46,
        47 => 47,
        48 => 48,
        49 => 49,
        50 => 50,
        51 => 51,
        52 => 52,
        53 => 53,
        54 => 54,
        55 => 55,
        56 => 55,
        57 => 57,
        58 => 58,
        59 => 59,
        60 => 60,
        61 => 61,
        62 => 62,
        63 => 63,
        64 => 64,
        65 => 65,
        66 => 66,
    );
    /* Beginning here are the reduction cases.  A typical example
    ** follows:
    **  #line <lineno> <grammarfile>
    **   function yy_r0($yymsp){ ... }           // User supplied code
    **  #line <lineno> <thisfile>
    */
#line 94 "Parser.y"
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
#line 1218 "Parser.php"
#line 122 "Parser.y"
    function yy_r4(){
	$this->_retvalue = array($this->yystack[$this->yyidx + -1]->minor);
	if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;
	}
    }
#line 1226 "Parser.php"
#line 130 "Parser.y"
    function yy_r5(){
	$this->_retvalue = 'table';
    }
#line 1231 "Parser.php"
#line 134 "Parser.y"
    function yy_r6(){
	$this->_retvalue = 'bar';
    }
#line 1236 "Parser.php"
#line 138 "Parser.y"
    function yy_r7(){
	$this->_retvalue = 'line';
    }
#line 1241 "Parser.php"
#line 142 "Parser.y"
    function yy_r8(){
	$this->_retvalue = 'pie';
    }
#line 1246 "Parser.php"
#line 147 "Parser.y"
    function yy_r9(){
	$this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
    }
#line 1251 "Parser.php"
#line 154 "Parser.y"
    function yy_r11(){
	$this->_retvalue = array($this->yystack[$this->yyidx + -1]->minor);
	if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue = array_merge($this->_retvalue, $this->yystack[$this->yyidx + 0]->minor);
	}
    }
#line 1259 "Parser.php"
#line 163 "Parser.y"
    function yy_r12(){
	if (!$this->yystack[$this->yyidx + -2]->minor) {
		$this->_retvalue = array();
	} else {
		$this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
	}
	$this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;
    }
#line 1269 "Parser.php"
#line 176 "Parser.y"
    function yy_r14(){
	if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue = new Statement\Part\Alias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
	} else {
		$this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
	}
    }
#line 1278 "Parser.php"
#line 191 "Parser.y"
    function yy_r16(){
	$this->_retvalue = $this->processQuoted($this->yystack[$this->yyidx + 0]->minor);
    }
#line 1283 "Parser.php"
#line 214 "Parser.y"
    function yy_r21(){
	$this->_retvalue = ($this->yystack[$this->yyidx + -1]->minor ? array($this->yystack[$this->yyidx + -1]->minor) : array());
	if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue = array_merge($this->_retvalue, $this->yystack[$this->yyidx + 0]->minor);
	}
    }
#line 1291 "Parser.php"
#line 225 "Parser.y"
    function yy_r23(){
	if (!$this->yystack[$this->yyidx + -2]->minor) {
		$this->_retvalue = array();
	} else {
		$this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
	}

	if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;
	}
    }
#line 1304 "Parser.php"
#line 241 "Parser.y"
    function yy_r25(){
	if ($this->yystack[$this->yyidx + 0]->minor instanceof Statement\Part\NullValue) {
		$this->_retvalue = false;
	} else {
		$this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
	}
    }
#line 1313 "Parser.php"
#line 278 "Parser.y"
    function yy_r30(){
	if ($this->yystack[$this->yyidx + -1]->minor instanceof Statement\Part\NullValue) {
		$this->_retvalue = false;
	} else if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue = new Statement\Part\Alias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
	} else {
		$this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
	}
    }
#line 1324 "Parser.php"
#line 302 "Parser.y"
    function yy_r33(){
	if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue = new Statement\Part\OrderDir($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
	} else {
		$this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
	}
    }
#line 1333 "Parser.php"
#line 313 "Parser.y"
    function yy_r34(){
	$this->_retvalue = 'ASC';
    }
#line 1338 "Parser.php"
#line 318 "Parser.y"
    function yy_r35(){
	$this->_retvalue = 'DESC';
    }
#line 1343 "Parser.php"
#line 340 "Parser.y"
    function yy_r39(){
	$this->_retvalue = array('limit' => intval($this->yystack[$this->yyidx + -1]->minor));
	if ($this->yystack[$this->yyidx + 0]->minor) {
		$this->_retvalue['offset'] = $this->yystack[$this->yyidx + 0]->minor;
	}
    }
#line 1351 "Parser.php"
#line 351 "Parser.y"
    function yy_r41(){
	$this->_retvalue = intval($this->yystack[$this->yyidx + 0]->minor);
    }
#line 1356 "Parser.php"
#line 359 "Parser.y"
    function yy_r43(){
	// this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	$this->_retvalue = new Statement\Part\BinaryComparison($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1364 "Parser.php"
#line 367 "Parser.y"
    function yy_r44(){
	// this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	$this->_retvalue = new Statement\Part\BinaryLogical($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1372 "Parser.php"
#line 375 "Parser.y"
    function yy_r45(){
	// this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;
	$expression = $this->yystack[$this->yyidx + 0]->minor;

	if ($expression[0] == 'interval') {
		$this->_retvalue = new Statement\Part\BinaryInterval($token, $this->yystack[$this->yyidx + -2]->minor, $expression[1], $expression[2]);
	} else {
		$this->_retvalue = new Statement\Part\BinaryMath($token, $this->yystack[$this->yyidx + -2]->minor, $expression[1]);
	}
    }
#line 1385 "Parser.php"
#line 388 "Parser.y"
    function yy_r46(){
	$this->_retvalue = array('interval', $this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1390 "Parser.php"
#line 393 "Parser.y"
    function yy_r47(){
	$this->_retvalue = array('expression', $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1395 "Parser.php"
#line 398 "Parser.y"
    function yy_r48(){
	// this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	$this->_retvalue = new Statement\Part\BinaryMath($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1403 "Parser.php"
#line 406 "Parser.y"
    function yy_r49(){
	$this->_retvalue = new Statement\Part\Like($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1408 "Parser.php"
#line 411 "Parser.y"
    function yy_r50(){
	$this->_retvalue = new Statement\Part\Like($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
#line 1413 "Parser.php"
#line 416 "Parser.y"
    function yy_r51(){
	$this->_retvalue = new Statement\Part\RegExp($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1418 "Parser.php"
#line 421 "Parser.y"
    function yy_r52(){
	$this->_retvalue = new Statement\Part\RegExp($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
#line 1423 "Parser.php"
#line 426 "Parser.y"
    function yy_r53(){
	$values = array($this->yystack[$this->yyidx + -2]->minor);
	if ($this->yystack[$this->yyidx + -1]->minor) {
		$values = array_merge($values, $this->yystack[$this->yyidx + -1]->minor);
	}
	$this->_retvalue = new Statement\Part\In($this->yystack[$this->yyidx + -5]->minor, $values);
    }
#line 1432 "Parser.php"
#line 435 "Parser.y"
    function yy_r54(){
	$values = array($this->yystack[$this->yyidx + -2]->minor);
	if ($this->yystack[$this->yyidx + -1]->minor) {
		$values = array_merge($values, $this->yystack[$this->yyidx + -1]->minor);
	}
	$this->_retvalue = new Statement\Part\In($this->yystack[$this->yyidx + -6]->minor, $values, false);
    }
#line 1441 "Parser.php"
#line 444 "Parser.y"
    function yy_r55(){
	// this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
	$token = $this->yystack[$this->yyidx + -1]->major;

	$this->_retvalue = new Statement\Part\UnaryOperator($token, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1449 "Parser.php"
#line 460 "Parser.y"
    function yy_r57(){
	$this->_retvalue = new Statement\Part\Parentheses($this->yystack[$this->yyidx + -1]->minor);
    }
#line 1454 "Parser.php"
#line 465 "Parser.y"
    function yy_r58(){
	if (!$this->yystack[$this->yyidx + -1]->minor) {
		$this->yystack[$this->yyidx + -1]->minor = array();
	}
	$this->_retvalue = new Statement\Part\FunctionCall($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + -1]->minor);
    }
#line 1462 "Parser.php"
#line 473 "Parser.y"
    function yy_r59(){
	$this->_retvalue = new Statement\Part\Column(explode('.', $this->yystack[$this->yyidx + 0]->minor));
    }
#line 1467 "Parser.php"
#line 478 "Parser.y"
    function yy_r60(){
	$this->_retvalue = new Statement\Part\String($this->yystack[$this->yyidx + 0]->minor);
    }
#line 1472 "Parser.php"
#line 483 "Parser.y"
    function yy_r61(){
	$this->_retvalue = new Statement\Part\String($this->processQuoted($this->yystack[$this->yyidx + 0]->minor));
    }
#line 1477 "Parser.php"
#line 488 "Parser.y"
    function yy_r62(){
	$value = substr($this->yystack[$this->yyidx + 0]->minor, 1, -1);
	$this->_retvalue = new Statement\Part\Placeholder($value);
    }
#line 1483 "Parser.php"
#line 494 "Parser.y"
    function yy_r63(){
	$this->_retvalue = new Statement\Part\AliasRef($this->yystack[$this->yyidx + 0]->minor);
    }
#line 1488 "Parser.php"
#line 499 "Parser.y"
    function yy_r64(){
	$this->_retvalue = new Statement\Part\AliasRef($this->processQuoted($this->yystack[$this->yyidx + 0]->minor));
    }
#line 1493 "Parser.php"
#line 504 "Parser.y"
    function yy_r65(){
	$this->_retvalue =  new Statement\Part\Number($this->yystack[$this->yyidx + 0]->minor + 0);
    }
#line 1498 "Parser.php"
#line 509 "Parser.y"
    function yy_r66(){
	$this->_retvalue = new Statement\Part\NullValue();
    }
#line 1503 "Parser.php"

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

	throw new Exception("Error parsing DPQL statement at line $this->line");
#line 1619 "Parser.php"
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
