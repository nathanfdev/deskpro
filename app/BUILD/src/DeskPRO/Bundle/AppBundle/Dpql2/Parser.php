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

namespace DeskPRO\Bundle\AppBundle\Dpql2;

use DeskPRO\Bundle\AppBundle\Dpql2\Statement\DpqlStatementFactory;

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
    public $string   = '';
    public $metadata = [];

    public function __construct($s, $m = [])
    {
        if ($s instanceof self) {
            $this->string   = $s->string;
            $this->metadata = $s->metadata;
        } else {
            $this->string = (string) $s;
            if ($m instanceof self) {
                $this->metadata = $m->metadata;
            } elseif (is_array($m)) {
                $this->metadata = $m;
            }
        }
    }

    public function __toString()
    {
        return $this->string;
    }

    public function offsetExists($offset)
    {
        return isset($this->metadata[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->metadata[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            if (isset($value[0])) {
                $x = ($value instanceof self) ?
                    $value->metadata : $value;
                $this->metadata = array_merge($this->metadata, $x);

                return;
            }
            $offset = count($this->metadata);
        }
        if ($value === null) {
            return;
        }
        if ($value instanceof self) {
            if ($value->metadata) {
                $this->metadata[$offset] = $value->metadata;
            }
        } elseif ($value) {
            $this->metadata[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->metadata[$offset]);
    }
}

/** The following structure represents a single element of the
 * parser's stack.  Information stored includes:.
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
}

// code external to the class is included here

// declare_class is output here
//line 1 "Parser.y"
class Parser //line 102 "Parser.php"
{
    /* First off, code is included which follows the "include_class" declaration
** in the input file. */
//line 10 "Parser.y"

    /**
     * Line number currently being parsed. This comes from the lexer.
     *
     * @var int
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
     * @return string String with quotes/escaping removed
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
            $string = substr($string, 0, $searchPos).substr($string, $searchPos + 1);
            ++$searchPos;
        } while (true);

        return $string;
    }
//line 182 "Parser.php"

/* Next is all token values, as class constants
*/
/*
** These constants (all generated automatically by the parser generator)
** specify the various kinds of tokens (terminals) that the parser
** understands.
**
** Each symbol here is a terminal symbol in the grammar.
*/
    const T_OP_OR          = 1;
    const T_OP_AND         = 2;
    const T_OP_NOT         = 3;
    const T_OP_EQ          = 4;
    const T_OP_NE          = 5;
    const T_OP_GT          = 6;
    const T_OP_GTEQ        = 7;
    const T_OP_LT          = 8;
    const T_OP_LTEQ        = 9;
    const T_OP_IN          = 10;
    const T_OP_LIKE        = 11;
    const T_OP_REGEXP      = 12;
    const T_OP_MINUS       = 13;
    const T_OP_PLUS        = 14;
    const T_OP_MULTIPLY    = 15;
    const T_OP_DIVIDE      = 16;
    const T_OP_U_MINUS     = 17;
    const T_OP_BANG        = 18;
    const T_SEMICOLON      = 19;
    const T_LEFT_PAREN     = 20;
    const T_RIGHT_PAREN    = 21;
    const T_OP_UNION       = 22;
    const T_OP_DISTINCT    = 23;
    const T_SELECT         = 24;
    const T_COMMA          = 25;
    const T_COLUMN_STAR    = 26;
    const T_AS             = 27;
    const T_LITERAL        = 28;
    const T_QUOTED         = 29;
    const T_FROM           = 30;
    const T_WHERE          = 31;
    const T_SPLIT          = 32;
    const T_BY             = 33;
    const T_GROUP          = 34;
    const T_WITH           = 35;
    const T_ROLLUP         = 36;
    const T_ORDER          = 37;
    const T_ASC            = 38;
    const T_DESC           = 39;
    const T_LIMIT          = 40;
    const T_NUMBER         = 41;
    const T_OFFSET         = 42;
    const T_OP_EXISTS      = 43;
    const T_INTERVAL       = 44;
    const T_COLUMN         = 45;
    const T_PLACEHOLDER    = 46;
    const T_VARIABLE       = 47;
    const T_AT             = 48;
    const T_NULL           = 49;
    const YY_NO_ACTION     = 219;
    const YY_ACCEPT_ACTION = 218;
    const YY_ERROR_ACTION  = 217;

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
    const YY_SZ_ACTTAB   = 299;
    public static $yy_action = [
 /*     0 */    21,   21,   70,   22,   22,   22,   22,   22,   22,   36,
 /*    10 */    30,   32,    3,    3,   28,   28,   21,   21,   70,   22,
 /*    20 */    22,   22,   22,   22,   22,   36,   30,   32,    3,    3,
 /*    30 */    28,   28,    3,    3,   28,   28,   92,  112,  111,   37,
 /*    40 */    31,   26,   74,   90,   21,   21,   70,   22,   22,   22,
 /*    50 */    22,   22,   22,   36,   30,   32,    3,    3,   28,   28,
 /*    60 */   218,   40,    1,   57,  114,   53,   21,   21,   70,   22,
 /*    70 */    22,   22,   22,   22,   22,   36,   30,   32,    3,    3,
 /*    80 */    28,   28,   21,   70,   22,   22,   22,   22,   22,   22,
 /*    90 */    36,   30,   32,    3,    3,   28,   28,   25,   39,  129,
 /*   100 */    39,   56,   16,  105,   16,   45,   35,   27,   14,  124,
 /*   110 */   125,   38,   25,   94,   24,   87,  123,  135,    1,   41,
 /*   120 */    23,   23,   91,  121,   14,  130,  131,   17,    1,   62,
 /*   130 */   106,   48,   60,  103,   89,  126,   33,   44,  100,  120,
 /*   140 */   109,  122,   73,  127,   70,   22,   22,   22,   22,   22,
 /*   150 */    22,   36,   30,   32,    3,    3,   28,   28,   25,   83,
 /*   160 */    60,   54,   81,   53,   34,   10,   18,   15,   27,   15,
 /*   170 */    47,   76,   97,   25,   55,   24,   17,   78,   47,   58,
 /*   180 */    47,   88,   59,   91,  121,  101,  133,  113,   93,   11,
 /*   190 */    19,  115,   20,    7,   82,  128,  126,   98,   44,   79,
 /*   200 */   120,  109,  122,   73,  127,   25,    2,   50,   99,    9,
 /*   210 */    23,  119,   12,  117,   13,   27,    8,   66,   86,  105,
 /*   220 */    25,   84,    5,   74,   63,  137,   14,  108,  134,    6,
 /*   230 */    91,  121,  136,    4,  104,   51,   72,   75,  116,  102,
 /*   240 */    71,   68,   95,  126,   85,   44,   29,  120,  109,  122,
 /*   250 */    73,  127,   25,   77,  118,   52,   64,   43,  110,   65,
 /*   260 */    69,   59,   27,   67,   49,  107,   47,   25,   96,   24,
 /*   270 */    45,   61,   80,   42,  132,  161,   46,   91,  121,  161,
 /*   280 */   161,  161,  161,  161,  161,  161,  161,  161,  161,  161,
 /*   290 */   126,  161,   44,  161,  120,  109,  122,   73,  127,
    ];
    public static $yy_lookahead = [
 /*     0 */     1,    2,    3,    4,    5,    6,    7,    8,    9,   10,
 /*    10 */    11,   12,   13,   14,   15,   16,    1,    2,    3,    4,
 /*    20 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*    30 */    15,   16,   13,   14,   15,   16,   28,   38,   39,   10,
 /*    40 */    11,   12,   27,   62,    1,    2,    3,    4,    5,    6,
 /*    50 */     7,    8,    9,   10,   11,   12,   13,   14,   15,   16,
 /*    60 */    51,   52,   24,   54,   21,   56,    1,    2,    3,    4,
 /*    70 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*    80 */    15,   16,    2,    3,    4,    5,    6,    7,    8,    9,
 /*    90 */    10,   11,   12,   13,   14,   15,   16,    3,   64,   65,
 /*   100 */    64,   65,   68,   19,   68,   20,   22,   13,   20,   28,
 /*   110 */    29,   23,   18,   28,   20,   37,   21,   21,   24,   62,
 /*   120 */    25,   25,   28,   29,   20,   28,   29,   68,   24,   68,
 /*   130 */    71,   57,   68,   69,   74,   41,   30,   43,   77,   45,
 /*   140 */    46,   47,   48,   49,    3,    4,    5,    6,    7,    8,
 /*   150 */     9,   10,   11,   12,   13,   14,   15,   16,    3,   52,
 /*   160 */    68,   69,   72,   56,   55,   20,   68,   68,   13,   68,
 /*   170 */    56,   66,   73,   18,   73,   20,   68,   79,   56,   71,
 /*   180 */    56,   70,   68,   28,   29,   67,   67,   75,   21,   25,
 /*   190 */    68,   68,   68,   33,   41,   21,   41,   36,   43,   44,
 /*   200 */    45,   46,   47,   48,   49,    3,   25,   41,   21,   25,
 /*   210 */    25,   76,   33,   41,   25,   13,   33,   68,   42,   19,
 /*   220 */    18,   78,   20,   27,   68,   54,   20,   64,   26,   20,
 /*   230 */    28,   29,   64,   20,   54,   58,   78,   40,   68,   53,
 /*   240 */    78,   68,   64,   41,   32,   43,   31,   45,   46,   47,
 /*   250 */    48,   49,    3,   35,   53,   61,   68,   58,   68,   68,
 /*   260 */    68,   68,   13,   68,   57,   67,   56,   18,   64,   20,
 /*   270 */    20,   68,   34,   59,   63,   80,   60,   28,   29,   80,
 /*   280 */    80,   80,   80,   80,   80,   80,   80,   80,   80,   80,
 /*   290 */    41,   80,   43,   80,   45,   46,   47,   48,   49,
];
    const YY_SHIFT_USE_DFLT      = -2;
    const YY_SHIFT_MAX           = 91;
    public static $yy_shift_ofst = [
 /*     0 */   104,  202,  202,  155,   94,   94,   94,  249,  249,  249,
 /*    10 */   249,  249,  249,  249,   38,   -1,   15,   15,   65,   65,
 /*    20 */    65,  249,  249,  249,  249,  249,  249,  249,  249,  249,
 /*    30 */   249,  249,  249,   85,   84,   88,  213,  209,  206,  196,
 /*    40 */   200,  197,  238,  212,  250,   38,  218,  106,  215,  215,
 /*    50 */   176,   78,   78,  106,   -2,   -2,   -2,   -2,   -2,   43,
 /*    60 */    65,   65,   65,   65,   80,  141,  141,   19,   19,   19,
 /*    70 */    29,   95,   96,   81,   97,  166,  181,  161,  174,  153,
 /*    80 */   160,  164,    8,  187,  185,  179,  172,  183,  189,  184,
 /*    90 */   167,  145,
];
    const YY_REDUCE_USE_DFLT      = -20;
    const YY_REDUCE_MAX           = 58;
    public static $yy_reduce_ofst = [
 /*     0 */     9,   36,   34,   61,  122,  114,  124,  108,  101,   99,
 /*    10 */    98,   59,   92,   64,  107,  112,  119,  118,  143,  158,
 /*    20 */   162,  188,  192,  203,  193,  170,  149,  123,  190,  156,
 /*    30 */   195,  191,  173,  178,  186,  180,  168,  163,  171,  198,
 /*    40 */   201,  211,  216,  214,  204,  210,  194,  207,  199,  177,
 /*    50 */   135,  -19,   57,   74,  111,   60,  105,  109,   90,
];
    public static $yyExpectedTokens = [
        /* 0 */ [20, 24],
        /* 1 */ [3, 13, 18, 20, 26, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 2 */ [3, 13, 18, 20, 26, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 3 */ [3, 13, 18, 20, 28, 29, 41, 43, 44, 45, 46, 47, 48, 49],
        /* 4 */ [3, 13, 18, 20, 24, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 5 */ [3, 13, 18, 20, 24, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 6 */ [3, 13, 18, 20, 24, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 7 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 8 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 9 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 10 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 11 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 12 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 13 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 14 */ [24],
        /* 15 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 38, 39],
        /* 16 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 27],
        /* 17 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 27],
        /* 18 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 19 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 20 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 21 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 22 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 23 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 24 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 25 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 26 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 27 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 28 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 29 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 30 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 31 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 32 */ [3, 13, 18, 20, 28, 29, 41, 43, 45, 46, 47, 48, 49],
        /* 33 */ [20, 28],
        /* 34 */ [19, 22],
        /* 35 */ [20, 23],
        /* 36 */ [20],
        /* 37 */ [20],
        /* 38 */ [20],
        /* 39 */ [27],
        /* 40 */ [19],
        /* 41 */ [40],
        /* 42 */ [34],
        /* 43 */ [32],
        /* 44 */ [20],
        /* 45 */ [24],
        /* 46 */ [35],
        /* 47 */ [30],
        /* 48 */ [31],
        /* 49 */ [31],
        /* 50 */ [42],
        /* 51 */ [37],
        /* 52 */ [37],
        /* 53 */ [30],
        /* 54 */ [],
        /* 55 */ [],
        /* 56 */ [],
        /* 57 */ [],
        /* 58 */ [],
        /* 59 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 21],
        /* 60 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 61 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 62 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 63 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 64 */ [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 65 */ [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 66 */ [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 67 */ [13, 14, 15, 16],
        /* 68 */ [13, 14, 15, 16],
        /* 69 */ [13, 14, 15, 16],
        /* 70 */ [10, 11, 12],
        /* 71 */ [21, 25],
        /* 72 */ [21, 25],
        /* 73 */ [28, 29],
        /* 74 */ [28, 29],
        /* 75 */ [41],
        /* 76 */ [25],
        /* 77 */ [36],
        /* 78 */ [21],
        /* 79 */ [41],
        /* 80 */ [33],
        /* 81 */ [25],
        /* 82 */ [28],
        /* 83 */ [21],
        /* 84 */ [25],
        /* 85 */ [33],
        /* 86 */ [41],
        /* 87 */ [33],
        /* 88 */ [25],
        /* 89 */ [25],
        /* 90 */ [21],
        /* 91 */ [20],
        /* 92 */ [],
        /* 93 */ [],
        /* 94 */ [],
        /* 95 */ [],
        /* 96 */ [],
        /* 97 */ [],
        /* 98 */ [],
        /* 99 */ [],
        /* 100 */ [],
        /* 101 */ [],
        /* 102 */ [],
        /* 103 */ [],
        /* 104 */ [],
        /* 105 */ [],
        /* 106 */ [],
        /* 107 */ [],
        /* 108 */ [],
        /* 109 */ [],
        /* 110 */ [],
        /* 111 */ [],
        /* 112 */ [],
        /* 113 */ [],
        /* 114 */ [],
        /* 115 */ [],
        /* 116 */ [],
        /* 117 */ [],
        /* 118 */ [],
        /* 119 */ [],
        /* 120 */ [],
        /* 121 */ [],
        /* 122 */ [],
        /* 123 */ [],
        /* 124 */ [],
        /* 125 */ [],
        /* 126 */ [],
        /* 127 */ [],
        /* 128 */ [],
        /* 129 */ [],
        /* 130 */ [],
        /* 131 */ [],
        /* 132 */ [],
        /* 133 */ [],
        /* 134 */ [],
        /* 135 */ [],
        /* 136 */ [],
        /* 137 */ [],
];
    public static $yy_default = [
 /*     0 */   217,  217,  217,  217,  217,  217,  217,  217,  217,  217,
 /*    10 */   214,  217,  217,  217,  217,  178,  156,  156,  216,  216,
 /*    20 */   216,  217,  217,  217,  217,  217,  217,  217,  217,  217,
 /*    30 */   217,  217,  217,  217,  141,  217,  217,  217,  217,  156,
 /*    40 */   141,  182,  167,  162,  217,  217,  172,  217,  160,  160,
 /*    50 */   184,  174,  174,  217,  164,  180,  150,  145,  169,  217,
 /*    60 */   165,  215,  190,  159,  187,  193,  195,  192,  194,  186,
 /*    70 */   217,  217,  217,  217,  217,  217,  148,  217,  217,  217,
 /*    80 */   217,  166,  217,  217,  213,  217,  217,  217,  161,  173,
 /*    90 */   217,  205,  189,  147,  157,  158,  185,  179,  171,  142,
 /*   100 */   188,  170,  139,  163,  143,  140,  168,  151,  199,  207,
 /*   110 */   191,  177,  176,  175,  202,  200,  201,  183,  138,  181,
 /*   120 */   204,  206,  208,  197,  209,  210,  211,  212,  203,  149,
 /*   130 */   154,  155,  146,  152,  153,  196,  198,  144,
];
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
    const YYNOCODE      = 81;
    const YYSTACKDEPTH  = 100;
    const YYNSTATE      = 138;
    const YYNRULE       = 79;
    const YYERRORSYMBOL = 50;
    const YYERRSYMDT    = 'yy0';
    const YYFALLBACK    = 0;
    /** The next table maps tokens into fallback tokens.  If a construct
     * like the following:.
     *
     *      %fallback ID X Y Z.
     *
     * appears in the grammer, then ID becomes a fallback token for X, Y,
     * and Z.  Whenever one of the tokens X, Y, or Z is input to the parser
     * but it does not parse, the type of the token is changed to ID and
     * the parse is retried before an error is thrown.
     */
    public static $yyFallback = [
    ];
    /**
     * Turn parser tracing on by giving a stream to which to write the trace
     * and a prompt to preface each trace message.  Tracing is turned off
     * by making either argument NULL.
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
     *
     * @param resource
     * @param string
     */
    public static function Trace($TraceFILE, $zTracePrompt)
    {
        if (!$TraceFILE) {
            $zTracePrompt = 0;
        } elseif (!$zTracePrompt) {
            $TraceFILE = 0;
        }
        self::$yyTraceFILE   = $TraceFILE;
        self::$yyTracePrompt = $zTracePrompt;
    }

    /**
     * Output debug information to output (php://output stream).
     */
    public static function PrintTrace()
    {
        self::$yyTraceFILE   = fopen('php://output', 'w');
        self::$yyTracePrompt = '';
    }

    /**
     * @var resource|0
     */
    public static $yyTraceFILE;
    /**
     * String to prepend to debug output.
     *
     * @var string|0
     */
    public static $yyTracePrompt;
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
    public $yystack = [];  /* The parser's stack */

    /**
     * For tracing shifts, the names of all terminals and nonterminals
     * are required.  The following table supplies these names.
     *
     * @var array
     */
    public static $yyTokenName = [
  '$',             'OP_OR',         'OP_AND',        'OP_NOT',
  'OP_EQ',         'OP_NE',         'OP_GT',         'OP_GTEQ',
  'OP_LT',         'OP_LTEQ',       'OP_IN',         'OP_LIKE',
  'OP_REGEXP',     'OP_MINUS',      'OP_PLUS',       'OP_MULTIPLY',
  'OP_DIVIDE',     'OP_U_MINUS',    'OP_BANG',       'SEMICOLON',
  'LEFT_PAREN',    'RIGHT_PAREN',   'OP_UNION',      'OP_DISTINCT',
  'SELECT',        'COMMA',         'COLUMN_STAR',   'AS',
  'LITERAL',       'QUOTED',        'FROM',          'WHERE',
  'SPLIT',         'BY',            'GROUP',         'WITH',
  'ROLLUP',        'ORDER',         'ASC',           'DESC',
  'LIMIT',         'NUMBER',        'OFFSET',        'OP_EXISTS',
  'INTERVAL',      'COLUMN',        'PLACEHOLDER',   'VARIABLE',
  'AT',            'NULL',          'error',         'start',
  'select_query_part',  'trailing_semicolon',  'select_paren',  'select_union',
  'select_clause',  'from_clause',   'where_clause',  'split_clause',
  'group_clause',  'with_rollup_clause',  'order_clause',  'limit_clause',
  'select_subquery_part',  'select_field',  'select_fields_extra',  'alias_optional',
  'expression',    'split_expression',  'split_expressions_extra',  'group_expression',
  'group_expressions_extra',  'order_expression',  'comma_order_expression_opt',  'direction_opt',
  'limit_offset_opt',  'interval_expression',  'comma_expressions_opt',  'func_args',
    ];

    /**
     * For tracing reduce actions, the names of all rules are required.
     *
     * @var array
     */
    public static $yyRuleName = [
 /*   0 */ 'start ::= select_query_part trailing_semicolon',
 /*   1 */ 'start ::= select_paren select_union trailing_semicolon',
 /*   2 */ 'trailing_semicolon ::= SEMICOLON',
 /*   3 */ 'trailing_semicolon ::=',
 /*   4 */ 'select_paren ::= LEFT_PAREN select_query_part RIGHT_PAREN',
 /*   5 */ 'select_union ::= select_union OP_UNION select_paren',
 /*   6 */ 'select_union ::= select_union OP_UNION OP_DISTINCT select_paren',
 /*   7 */ 'select_union ::=',
 /*   8 */ 'select_query_part ::= select_clause from_clause where_clause split_clause group_clause with_rollup_clause order_clause limit_clause',
 /*   9 */ 'select_subquery_part ::= LEFT_PAREN select_clause from_clause where_clause order_clause RIGHT_PAREN',
 /*  10 */ 'select_clause ::= SELECT select_field select_fields_extra',
 /*  11 */ 'select_fields_extra ::= select_fields_extra COMMA select_field',
 /*  12 */ 'select_fields_extra ::=',
 /*  13 */ 'select_field ::= select_subquery_part alias_optional',
 /*  14 */ 'select_field ::= expression alias_optional',
 /*  15 */ 'select_field ::= COLUMN_STAR',
 /*  16 */ 'alias_optional ::= AS LITERAL',
 /*  17 */ 'alias_optional ::= AS QUOTED',
 /*  18 */ 'alias_optional ::=',
 /*  19 */ 'from_clause ::= FROM LITERAL',
 /*  20 */ 'from_clause ::= FROM select_subquery_part',
 /*  21 */ 'where_clause ::= WHERE expression',
 /*  22 */ 'where_clause ::=',
 /*  23 */ 'split_clause ::= SPLIT BY split_expression split_expressions_extra',
 /*  24 */ 'split_clause ::=',
 /*  25 */ 'split_expressions_extra ::= split_expressions_extra COMMA split_expression',
 /*  26 */ 'split_expressions_extra ::=',
 /*  27 */ 'split_expression ::= expression',
 /*  28 */ 'group_clause ::= GROUP BY group_expression group_expressions_extra',
 /*  29 */ 'group_clause ::=',
 /*  30 */ 'group_expressions_extra ::= group_expressions_extra COMMA group_expression',
 /*  31 */ 'group_expressions_extra ::=',
 /*  32 */ 'group_expression ::= expression alias_optional',
 /*  33 */ 'with_rollup_clause ::= WITH ROLLUP',
 /*  34 */ 'with_rollup_clause ::=',
 /*  35 */ 'order_clause ::= ORDER BY order_expression comma_order_expression_opt',
 /*  36 */ 'order_clause ::=',
 /*  37 */ 'order_expression ::= expression direction_opt',
 /*  38 */ 'direction_opt ::= ASC',
 /*  39 */ 'direction_opt ::= DESC',
 /*  40 */ 'direction_opt ::=',
 /*  41 */ 'comma_order_expression_opt ::= comma_order_expression_opt COMMA order_expression',
 /*  42 */ 'comma_order_expression_opt ::=',
 /*  43 */ 'limit_clause ::= LIMIT NUMBER limit_offset_opt',
 /*  44 */ 'limit_clause ::=',
 /*  45 */ 'limit_offset_opt ::= OFFSET NUMBER',
 /*  46 */ 'limit_offset_opt ::=',
 /*  47 */ 'expression ::= OP_EXISTS select_subquery_part',
 /*  48 */ 'expression ::= expression OP_EQ|OP_NE|OP_GT|OP_GTEQ|OP_LT|OP_LTEQ expression',
 /*  49 */ 'expression ::= expression OP_OR|OP_AND expression',
 /*  50 */ 'expression ::= expression OP_MINUS|OP_PLUS interval_expression',
 /*  51 */ 'interval_expression ::= INTERVAL NUMBER LITERAL',
 /*  52 */ 'interval_expression ::= expression',
 /*  53 */ 'expression ::= expression OP_MULTIPLY|OP_DIVIDE expression',
 /*  54 */ 'expression ::= expression OP_LIKE expression',
 /*  55 */ 'expression ::= expression OP_NOT OP_LIKE expression',
 /*  56 */ 'expression ::= expression OP_REGEXP expression',
 /*  57 */ 'expression ::= expression OP_NOT OP_REGEXP expression',
 /*  58 */ 'expression ::= expression OP_IN LEFT_PAREN expression comma_expressions_opt RIGHT_PAREN',
 /*  59 */ 'expression ::= expression OP_NOT OP_IN LEFT_PAREN expression comma_expressions_opt RIGHT_PAREN',
 /*  60 */ 'expression ::= expression OP_IN select_subquery_part',
 /*  61 */ 'expression ::= expression OP_NOT OP_IN select_subquery_part',
 /*  62 */ 'expression ::= OP_MINUS expression',
 /*  63 */ 'expression ::= OP_BANG|OP_NOT expression',
 /*  64 */ 'expression ::= LEFT_PAREN expression RIGHT_PAREN',
 /*  65 */ 'expression ::= LITERAL LEFT_PAREN func_args RIGHT_PAREN',
 /*  66 */ 'expression ::= COLUMN',
 /*  67 */ 'expression ::= LITERAL',
 /*  68 */ 'expression ::= QUOTED',
 /*  69 */ 'expression ::= PLACEHOLDER',
 /*  70 */ 'expression ::= VARIABLE',
 /*  71 */ 'expression ::= AT LITERAL',
 /*  72 */ 'expression ::= AT QUOTED',
 /*  73 */ 'expression ::= NUMBER',
 /*  74 */ 'expression ::= NULL',
 /*  75 */ 'func_args ::= expression comma_expressions_opt',
 /*  76 */ 'func_args ::=',
 /*  77 */ 'comma_expressions_opt ::= comma_expressions_opt COMMA expression',
 /*  78 */ 'comma_expressions_opt ::=',
    ];

    /**
     * This function returns the symbolic name associated with a token
     * value.
     *
     * @param int
     *
     * @return string
     */
    public function tokenName($tokenType)
    {
        if ($tokenType === 0) {
            return 'End of Input';
        }
        if ($tokenType > 0 && $tokenType < count(self::$yyTokenName)) {
            return self::$yyTokenName[$tokenType];
        } else {
            return 'Unknown';
        }
    }

    /**
     * The following function deletes the value associated with a
     * symbol.  The symbol can be either a terminal or nonterminal.
     *
     * @param int the symbol code
     * @param mixed the symbol's value
     */
    public static function yy_destructor($yymajor, $yypminor)
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
     *
     * @param ParseyyParser
     *
     * @return int
     */
    public function yy_pop_parser_stack()
    {
        if (!count($this->yystack)) {
            return;
        }
        $yytos = array_pop($this->yystack);
        if (self::$yyTraceFILE && $this->yyidx >= 0) {
            fwrite(self::$yyTraceFILE,
                self::$yyTracePrompt.'Popping '.self::$yyTokenName[$yytos->major].
                    "\n");
        }
        $yymajor = $yytos->major;
        self::yy_destructor($yymajor, $yytos->minor);
        --$this->yyidx;

        return $yymajor;
    }

    /**
     * Deallocate and destroy a parser.  Destructors are all called for
     * all stack elements before shutting the parser down.
     */
    public function __destruct()
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
     * possible lookahead tokens.
     *
     * @param int
     *
     * @return array
     */
    public function yy_get_expected_tokens($token)
    {
        $state    = $this->yystack[$this->yyidx]->stateno;
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
                        $this->yyidx   = $yyidx;
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
                            $this->yyidx   = $yyidx;
                            $this->yystack = $stack;

                            return array_unique($expected);
                        }
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        ++$this->yyidx;
                        $x                           = new ParseyyStackEntry();
                        $x->stateno                  = $nextstate;
                        $x->major                    = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx   = $yyidx;
                        $this->yystack = $stack;
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return array_unique($expected);
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx   = $yyidx;
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
     *
     * @param int
     *
     * @return bool
     */
    public function yy_is_expected_token($token)
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
                        $this->yyidx   = $yyidx;
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
                        $this->yyidx   = $yyidx;
                        $this->yystack = $stack;

                        return true;
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        ++$this->yyidx;
                        $x                           = new ParseyyStackEntry();
                        $x->stateno                  = $nextstate;
                        $x->major                    = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx   = $yyidx;
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
                        $this->yyidx   = $yyidx;
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
        $this->yyidx   = $yyidx;
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
     *
     * @param int The look-ahead token
     */
    public function yy_find_shift_action($iLookAhead)
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
                    fwrite(self::$yyTraceFILE, self::$yyTracePrompt.'FALLBACK '.
                        self::$yyTokenName[$iLookAhead].' => '.
                        self::$yyTokenName[$iFallback]."\n");
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
     *
     * @param int Current state number
     * @param int The look-ahead token
     */
    public function yy_find_reduce_action($stateno, $iLookAhead)
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
     *
     * @param int The new state to shift in
     * @param int The major token to shift in
     * @param mixed the minor token to shift in
     */
    public function yy_shift($yyNewState, $yyMajor, $yypMinor)
    {
        ++$this->yyidx;
        if ($this->yyidx >= self::YYSTACKDEPTH) {
            --$this->yyidx;
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
        $yytos          = new ParseyyStackEntry();
        $yytos->stateno = $yyNewState;
        $yytos->major   = $yyMajor;
        $yytos->minor   = $yypMinor;
        array_push($this->yystack, $yytos);
        if (self::$yyTraceFILE && $this->yyidx > 0) {
            fprintf(self::$yyTraceFILE, "%sShift %d\n", self::$yyTracePrompt,
                $yyNewState);
            fprintf(self::$yyTraceFILE, '%sStack:', self::$yyTracePrompt);
            for ($i = 1; $i <= $this->yyidx; ++$i) {
                fprintf(self::$yyTraceFILE, ' %s',
                    self::$yyTokenName[$this->yystack[$i]->major]);
            }
            fwrite(self::$yyTraceFILE, "\n");
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
    public static $yyRuleInfo = [
  ['lhs' => 51, 'rhs' => 2],
  ['lhs' => 51, 'rhs' => 3],
  ['lhs' => 53, 'rhs' => 1],
  ['lhs' => 53, 'rhs' => 0],
  ['lhs' => 54, 'rhs' => 3],
  ['lhs' => 55, 'rhs' => 3],
  ['lhs' => 55, 'rhs' => 4],
  ['lhs' => 55, 'rhs' => 0],
  ['lhs' => 52, 'rhs' => 8],
  ['lhs' => 64, 'rhs' => 6],
  ['lhs' => 56, 'rhs' => 3],
  ['lhs' => 66, 'rhs' => 3],
  ['lhs' => 66, 'rhs' => 0],
  ['lhs' => 65, 'rhs' => 2],
  ['lhs' => 65, 'rhs' => 2],
  ['lhs' => 65, 'rhs' => 1],
  ['lhs' => 67, 'rhs' => 2],
  ['lhs' => 67, 'rhs' => 2],
  ['lhs' => 67, 'rhs' => 0],
  ['lhs' => 57, 'rhs' => 2],
  ['lhs' => 57, 'rhs' => 2],
  ['lhs' => 58, 'rhs' => 2],
  ['lhs' => 58, 'rhs' => 0],
  ['lhs' => 59, 'rhs' => 4],
  ['lhs' => 59, 'rhs' => 0],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 0],
  ['lhs' => 69, 'rhs' => 1],
  ['lhs' => 60, 'rhs' => 4],
  ['lhs' => 60, 'rhs' => 0],
  ['lhs' => 72, 'rhs' => 3],
  ['lhs' => 72, 'rhs' => 0],
  ['lhs' => 71, 'rhs' => 2],
  ['lhs' => 61, 'rhs' => 2],
  ['lhs' => 61, 'rhs' => 0],
  ['lhs' => 62, 'rhs' => 4],
  ['lhs' => 62, 'rhs' => 0],
  ['lhs' => 73, 'rhs' => 2],
  ['lhs' => 75, 'rhs' => 1],
  ['lhs' => 75, 'rhs' => 1],
  ['lhs' => 75, 'rhs' => 0],
  ['lhs' => 74, 'rhs' => 3],
  ['lhs' => 74, 'rhs' => 0],
  ['lhs' => 63, 'rhs' => 3],
  ['lhs' => 63, 'rhs' => 0],
  ['lhs' => 76, 'rhs' => 2],
  ['lhs' => 76, 'rhs' => 0],
  ['lhs' => 68, 'rhs' => 2],
  ['lhs' => 68, 'rhs' => 3],
  ['lhs' => 68, 'rhs' => 3],
  ['lhs' => 68, 'rhs' => 3],
  ['lhs' => 77, 'rhs' => 3],
  ['lhs' => 77, 'rhs' => 1],
  ['lhs' => 68, 'rhs' => 3],
  ['lhs' => 68, 'rhs' => 3],
  ['lhs' => 68, 'rhs' => 4],
  ['lhs' => 68, 'rhs' => 3],
  ['lhs' => 68, 'rhs' => 4],
  ['lhs' => 68, 'rhs' => 6],
  ['lhs' => 68, 'rhs' => 7],
  ['lhs' => 68, 'rhs' => 3],
  ['lhs' => 68, 'rhs' => 4],
  ['lhs' => 68, 'rhs' => 2],
  ['lhs' => 68, 'rhs' => 2],
  ['lhs' => 68, 'rhs' => 3],
  ['lhs' => 68, 'rhs' => 4],
  ['lhs' => 68, 'rhs' => 1],
  ['lhs' => 68, 'rhs' => 1],
  ['lhs' => 68, 'rhs' => 1],
  ['lhs' => 68, 'rhs' => 1],
  ['lhs' => 68, 'rhs' => 1],
  ['lhs' => 68, 'rhs' => 2],
  ['lhs' => 68, 'rhs' => 2],
  ['lhs' => 68, 'rhs' => 1],
  ['lhs' => 68, 'rhs' => 1],
  ['lhs' => 79, 'rhs' => 2],
  ['lhs' => 79, 'rhs' => 0],
  ['lhs' => 78, 'rhs' => 3],
  ['lhs' => 78, 'rhs' => 0],
    ];

    /**
     * The following table contains a mapping of reduce action to method name
     * that handles the reduction.
     *
     * If a rule is not set, it has no handler.
     */
    public static $yyReduceMap = [
        0  => 0,
        1  => 1,
        4  => 4,
        5  => 5,
        6  => 6,
        8  => 8,
        9  => 9,
        10 => 10,
        35 => 10,
        75 => 10,
        11 => 11,
        41 => 11,
        77 => 11,
        13 => 13,
        14 => 14,
        15 => 15,
        16 => 16,
        19 => 16,
        21 => 16,
        17 => 17,
        20 => 20,
        23 => 23,
        28 => 23,
        25 => 25,
        30 => 25,
        27 => 27,
        32 => 32,
        33 => 33,
        37 => 37,
        38 => 38,
        39 => 39,
        43 => 43,
        45 => 45,
        47 => 47,
        48 => 48,
        49 => 49,
        50 => 50,
        51 => 51,
        52 => 52,
        53 => 53,
        54 => 54,
        55 => 55,
        56 => 56,
        57 => 57,
        58 => 58,
        59 => 59,
        60 => 60,
        61 => 61,
        62 => 62,
        63 => 62,
        64 => 64,
        65 => 65,
        66 => 66,
        67 => 67,
        68 => 68,
        69 => 69,
        70 => 70,
        71 => 71,
        72 => 72,
        73 => 73,
        74 => 74,
    ];
    /* Beginning here are the reduction cases.  A typical example
    ** follows:
    **  #line <lineno> <grammarfile>
    **   function yy_r0($yymsp){ ... }           // User supplied code
    **  #line <lineno> <thisfile>
    */
//line 94 "Parser.y"
    public function yy_r0()
    {
        $res = $this->statementFactory->createQuery([['ANY', $this->yystack[$this->yyidx + -1]->minor]]);

        $this->_result = $res;
    }
//line 1276 "Parser.php"
//line 101 "Parser.y"
    public function yy_r1()
    {
        $parts = [['ANY', $this->yystack[$this->yyidx + -2]->minor]];
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $parts = array_merge($parts, $this->yystack[$this->yyidx + -1]->minor);
        }

        $res = $this->statementFactory->createQuery($parts);

        $this->_result = $res;
    }
//line 1288 "Parser.php"
//line 116 "Parser.y"
    public function yy_r4()
    {
        $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
    }
//line 1293 "Parser.php"
//line 121 "Parser.y"
    public function yy_r5()
    {
        if (!$this->yystack[$this->yyidx + -2]->minor) {
            $this->_retvalue = [];
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
        }

        $this->_retvalue[] = ['ANY', $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1304 "Parser.php"
//line 132 "Parser.y"
    public function yy_r6()
    {
        if (!$this->yystack[$this->yyidx + -3]->minor) {
            $this->_retvalue = [];
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor;
        }

        $this->_retvalue[] = ['DISTINCT', $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1315 "Parser.php"
//line 146 "Parser.y"
    public function yy_r8()
    {
        $q = $this->statementFactory->createSelectPart($this->yystack[$this->yyidx + -7]->minor, $this->yystack[$this->yyidx + -6]->minor);

        if ($this->yystack[$this->yyidx + -5]->minor) {
            $q->setWhere($this->yystack[$this->yyidx + -5]->minor);
        }
        if ($this->yystack[$this->yyidx + -4]->minor) {
            $q->setSplitBy($this->yystack[$this->yyidx + -4]->minor);
        }
        if ($this->yystack[$this->yyidx + -3]->minor) {
            $q->setGroupBy($this->yystack[$this->yyidx + -3]->minor);
        }
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $q->setOrderBy($this->yystack[$this->yyidx + -1]->minor);
        }
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $q->setLimitAmount($this->yystack[$this->yyidx + 0]->minor['limit']);
            if (isset($this->yystack[$this->yyidx + 0]->minor['offset'])) {
                $q->setLimitOffset($this->yystack[$this->yyidx + 0]->minor['offset']);
            }
        }
        if ($this->yystack[$this->yyidx + -2]->minor) {
            $q->setWithRollup(true);
        }

        $this->_retvalue = $q;
    }
//line 1344 "Parser.php"
//line 175 "Parser.y"
    public function yy_r9()
    {
        $q = $this->statementFactory->createSelectPart($this->yystack[$this->yyidx + -4]->minor, $this->yystack[$this->yyidx + -3]->minor);

        if ($this->yystack[$this->yyidx + -2]->minor) {
            $q->setWhere($this->yystack[$this->yyidx + -2]->minor);
        }
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $q->setOrderBy($this->yystack[$this->yyidx + -1]->minor);
        }

        $q->setIsSubquery(true);

        $this->_retvalue = $q;
    }
//line 1360 "Parser.php"
//line 191 "Parser.y"
    public function yy_r10()
    {
        $this->_retvalue = [$this->yystack[$this->yyidx + -1]->minor];
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = array_merge($this->_retvalue, $this->yystack[$this->yyidx + 0]->minor);
        }
    }
//line 1368 "Parser.php"
//line 200 "Parser.y"
    public function yy_r11()
    {
        if (!$this->yystack[$this->yyidx + -2]->minor) {
            $this->_retvalue = [];
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
        }
        $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;
    }
//line 1378 "Parser.php"
//line 211 "Parser.y"
    public function yy_r13()
    {
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = $this->statementFactory->createAlias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1387 "Parser.php"
//line 220 "Parser.y"
    public function yy_r14()
    {
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = $this->statementFactory->createAlias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1396 "Parser.php"
//line 229 "Parser.y"
    public function yy_r15()
    {
        $this->_retvalue = $this->statementFactory->createColumnStar(explode('.', $this->yystack[$this->yyidx + 0]->minor));
    }
//line 1401 "Parser.php"
//line 236 "Parser.y"
    public function yy_r16()
    {
        $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
    }
//line 1406 "Parser.php"
//line 240 "Parser.y"
    public function yy_r17()
    {
        $this->_retvalue = $this->processQuoted($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1411 "Parser.php"
//line 253 "Parser.y"
    public function yy_r20()
    {
        $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
    }
//line 1416 "Parser.php"
//line 265 "Parser.y"
    public function yy_r23()
    {
        $this->_retvalue = ($this->yystack[$this->yyidx + -1]->minor ? [$this->yystack[$this->yyidx + -1]->minor] : []);
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = array_merge($this->_retvalue, $this->yystack[$this->yyidx + 0]->minor);
        }
    }
//line 1424 "Parser.php"
//line 276 "Parser.y"
    public function yy_r25()
    {
        if (!$this->yystack[$this->yyidx + -2]->minor) {
            $this->_retvalue = [];
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
        }

        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;
        }
    }
//line 1437 "Parser.php"
//line 292 "Parser.y"
    public function yy_r27()
    {
        if ($this->yystack[$this->yyidx + 0]->minor instanceof Statement\Part\NullValue) {
            $this->_retvalue = false;
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
        }
    }
//line 1446 "Parser.php"
//line 329 "Parser.y"
    public function yy_r32()
    {
        if ($this->yystack[$this->yyidx + -1]->minor instanceof Statement\Part\NullValue) {
            $this->_retvalue = false;
        } elseif ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = $this->statementFactory->createAlias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1457 "Parser.php"
//line 342 "Parser.y"
    public function yy_r33()
    {
        $this->_retvalue = true;
    }
//line 1462 "Parser.php"
//line 361 "Parser.y"
    public function yy_r37()
    {
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = $this->statementFactory->createOrderDir($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1471 "Parser.php"
//line 372 "Parser.y"
    public function yy_r38()
    {
        $this->_retvalue = 'ASC';
    }
//line 1476 "Parser.php"
//line 377 "Parser.y"
    public function yy_r39()
    {
        $this->_retvalue = 'DESC';
    }
//line 1481 "Parser.php"
//line 399 "Parser.y"
    public function yy_r43()
    {
        $this->_retvalue = ['limit' => intval($this->yystack[$this->yyidx + -1]->minor)];
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue['offset'] = $this->yystack[$this->yyidx + 0]->minor;
        }
    }
//line 1489 "Parser.php"
//line 410 "Parser.y"
    public function yy_r45()
    {
        $this->_retvalue = intval($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1494 "Parser.php"
//line 416 "Parser.y"
    public function yy_r47()
    {
        $this->_retvalue = $this->statementFactory->createExists($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1499 "Parser.php"
//line 421 "Parser.y"
    public function yy_r48()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = $this->statementFactory->createBinaryComparison($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1507 "Parser.php"
//line 429 "Parser.y"
    public function yy_r49()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = $this->statementFactory->createBinaryLogical($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1515 "Parser.php"
//line 437 "Parser.y"
    public function yy_r50()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token      = $this->yystack[$this->yyidx + -1]->major;
        $expression = $this->yystack[$this->yyidx + 0]->minor;

        if ($expression[0] == 'interval') {
            $this->_retvalue = $this->statementFactory->createBinaryInterval($token, $this->yystack[$this->yyidx + -2]->minor, $expression[1], $expression[2]);
        } else {
            $this->_retvalue = $this->statementFactory->createBinaryMath($token, $this->yystack[$this->yyidx + -2]->minor, $expression[1]);
        }
    }
//line 1528 "Parser.php"
//line 450 "Parser.y"
    public function yy_r51()
    {
        $this->_retvalue = ['interval', $this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1533 "Parser.php"
//line 455 "Parser.y"
    public function yy_r52()
    {
        $this->_retvalue = ['expression', $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1538 "Parser.php"
//line 460 "Parser.y"
    public function yy_r53()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = $this->statementFactory->createBinaryMath($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1546 "Parser.php"
//line 468 "Parser.y"
    public function yy_r54()
    {
        $this->_retvalue = $this->statementFactory->createLike($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1551 "Parser.php"
//line 473 "Parser.y"
    public function yy_r55()
    {
        $this->_retvalue = $this->statementFactory->createLike($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
//line 1556 "Parser.php"
//line 478 "Parser.y"
    public function yy_r56()
    {
        $this->_retvalue = $this->statementFactory->createRegExp($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1561 "Parser.php"
//line 483 "Parser.y"
    public function yy_r57()
    {
        $this->_retvalue = $this->statementFactory->createRegExp($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
//line 1566 "Parser.php"
//line 488 "Parser.y"
    public function yy_r58()
    {
        $values = [$this->yystack[$this->yyidx + -2]->minor];
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $values = array_merge($values, $this->yystack[$this->yyidx + -1]->minor);
        }
        $this->_retvalue = $this->statementFactory->createIn($this->yystack[$this->yyidx + -5]->minor, $values);
    }
//line 1575 "Parser.php"
//line 497 "Parser.y"
    public function yy_r59()
    {
        $values = [$this->yystack[$this->yyidx + -2]->minor];
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $values = array_merge($values, $this->yystack[$this->yyidx + -1]->minor);
        }
        $this->_retvalue = $this->statementFactory->createIn($this->yystack[$this->yyidx + -6]->minor, $values, false);
    }
//line 1584 "Parser.php"
//line 506 "Parser.y"
    public function yy_r60()
    {
        $this->_retvalue = $this->statementFactory->createInSubquery($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1589 "Parser.php"
//line 511 "Parser.y"
    public function yy_r61()
    {
        $this->_retvalue = $this->statementFactory->createInSubquery($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
//line 1594 "Parser.php"
//line 516 "Parser.y"
    public function yy_r62()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = $this->statementFactory->createUnaryOperator($token, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1602 "Parser.php"
//line 532 "Parser.y"
    public function yy_r64()
    {
        $this->_retvalue = $this->statementFactory->createParentheses($this->yystack[$this->yyidx + -1]->minor);
    }
//line 1607 "Parser.php"
//line 537 "Parser.y"
    public function yy_r65()
    {
        if (!$this->yystack[$this->yyidx + -1]->minor) {
            $this->yystack[$this->yyidx + -1]->minor = [];
        }
        $this->_retvalue = $this->statementFactory->createFunctionCall($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + -1]->minor);
    }
//line 1615 "Parser.php"
//line 545 "Parser.y"
    public function yy_r66()
    {
        $this->_retvalue = $this->statementFactory->createColumn(explode('.', $this->yystack[$this->yyidx + 0]->minor));
    }
//line 1620 "Parser.php"
//line 550 "Parser.y"
    public function yy_r67()
    {
        $this->_retvalue = $this->statementFactory->createStringPart($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1625 "Parser.php"
//line 555 "Parser.y"
    public function yy_r68()
    {
        $this->_retvalue = $this->statementFactory->createStringPart($this->processQuoted($this->yystack[$this->yyidx + 0]->minor));
    }
//line 1630 "Parser.php"
//line 560 "Parser.y"
    public function yy_r69()
    {
        $value           = substr($this->yystack[$this->yyidx + 0]->minor, 1, -1);
        $this->_retvalue = $this->statementFactory->createPlaceholder($value);
    }
//line 1636 "Parser.php"
//line 566 "Parser.y"
    public function yy_r70()
    {
        $value           = substr($this->yystack[$this->yyidx + 0]->minor, 2, -1);
        $this->_retvalue = $this->statementFactory->createVariable($value);
    }
//line 1642 "Parser.php"
//line 572 "Parser.y"
    public function yy_r71()
    {
        $this->_retvalue = $this->statementFactory->createAliasRef($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1647 "Parser.php"
//line 577 "Parser.y"
    public function yy_r72()
    {
        $this->_retvalue = $this->statementFactory->createAliasRef($this->processQuoted($this->yystack[$this->yyidx + 0]->minor));
    }
//line 1652 "Parser.php"
//line 582 "Parser.y"
    public function yy_r73()
    {
        $this->_retvalue = $this->statementFactory->createNumber($this->yystack[$this->yyidx + 0]->minor + 0);
    }
//line 1657 "Parser.php"
//line 587 "Parser.y"
    public function yy_r74()
    {
        $this->_retvalue = $this->statementFactory->createNullValue();
    }
//line 1662 "Parser.php"

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
     *
     * @param int Number of the rule by which to reduce
     */
    public function yy_reduce($yyruleno)
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
            $this->{'yy_r'.self::$yyReduceMap[$yyruleno]}();
            $yy_lefthand_side = $this->_retvalue;
        }
        $yygoto = self::$yyRuleInfo[$yyruleno]['lhs'];
        $yysize = self::$yyRuleInfo[$yyruleno]['rhs'];
        $this->yyidx -= $yysize;
        for ($i = $yysize; $i; --$i) {
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
                ++$this->yyidx;
                $x                           = new ParseyyStackEntry();
                $x->stateno                  = $yyact;
                $x->major                    = $yygoto;
                $x->minor                    = $yy_lefthand_side;
                $this->yystack[$this->yyidx] = $x;
            } else {
                $this->yy_shift($yyact, $yygoto, $yy_lefthand_side);
            }
        } elseif ($yyact == self::YYNSTATE + self::YYNRULE + 1) {
            $this->yy_accept();
        }
    }

    /**
     * The following code executes when the parse fails.
     *
     * Code from %parse_fail is inserted here
     */
    public function yy_parse_failed()
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
     *
     * @param int The major type of the error token
     * @param mixed The minor type of the error token
     */
    public function yy_syntax_error($yymajor, $TOKEN)
    {
        //line 5 "Parser.y"

    throw new Exception("Error parsing DPQL statement at line $this->line (got $TOKEN)");
//line 1778 "Parser.php"
    }

    /**
     * The following is executed when the parser accepts.
     *
     * %parse_accept code is inserted here
     */
    public function yy_accept()
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
     */
    public function doParse($yymajor, $yytokenvalue)
    {
        //        $yyact;            /* The parser action. */
//        $yyendofinput;     /* True if we are at the end of input */
        $yyerrorhit = 0;   /* True if yymajor has invoked an error */

        /* (re)initialize the parser, if necessary */
        if ($this->yyidx === null || $this->yyidx < 0) {
            /* if ($yymajor == 0) return; // not sure why this was here... */
            $this->yyidx    = 0;
            $this->yyerrcnt = -1;
            $x              = new ParseyyStackEntry();
            $x->stateno     = 0;
            $x->major       = 0;
            $this->yystack  = [];
            array_push($this->yystack, $x);
        }
        $yyendofinput = ($yymajor == 0);

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
                --$this->yyerrcnt;
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
                    if ($yymx == self::YYERRORSYMBOL || $yyerrorhit) {
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
                        if ($this->yyidx < 0 || $yymajor == 0) {
                            $this->yy_destructor($yymajor, $yytokenvalue);
                            $this->yy_parse_failed();
                            $yymajor = self::YYNOCODE;
                        } elseif ($yymx != self::YYERRORSYMBOL) {
                            $u2 = 0;
                            $this->yy_shift($yyact, self::YYERRORSYMBOL, $u2);
                        }
                    }
                    $this->yyerrcnt = 3;
                    $yyerrorhit     = 1;
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
