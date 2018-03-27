<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\DpqlStatementFactory;

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
    const T_OP_ALL         = 23;
    const T_OP_DISTINCT    = 24;
    const T_SELECT         = 25;
    const T_COMMA          = 26;
    const T_COLUMN_STAR    = 27;
    const T_AS             = 28;
    const T_LITERAL        = 29;
    const T_QUOTED         = 30;
    const T_FROM           = 31;
    const T_WHERE          = 32;
    const T_SPLIT          = 33;
    const T_BY             = 34;
    const T_GROUP          = 35;
    const T_WITH           = 36;
    const T_ROLLUP         = 37;
    const T_ORDER          = 38;
    const T_ASC            = 39;
    const T_DESC           = 40;
    const T_LIMIT          = 41;
    const T_NUMBER         = 42;
    const T_OFFSET         = 43;
    const T_OP_EXISTS      = 44;
    const T_INTERVAL       = 45;
    const T_COLUMN         = 46;
    const T_PLACEHOLDER    = 47;
    const T_VARIABLE       = 48;
    const T_AT             = 49;
    const T_NULL           = 50;
    const YY_NO_ACTION     = 235;
    const YY_ACCEPT_ACTION = 234;
    const YY_ERROR_ACTION  = 233;

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
    const YY_SZ_ACTTAB   = 327;
    public static $yy_action = [
 /*     0 */    26,   26,   74,   18,   18,   18,   18,   18,   18,   41,
 /*    10 */    19,   25,    4,    4,   17,   17,   26,   26,   74,   18,
 /*    20 */    18,   18,   18,   18,   18,   41,   19,   25,    4,    4,
 /*    30 */    17,   17,  139,   15,   78,   52,  103,   86,  109,  110,
 /*    40 */    26,   26,   74,   18,   18,   18,   18,   18,   18,   41,
 /*    50 */    19,   25,    4,    4,   17,   17,    4,    4,   17,   17,
 /*    60 */   169,  104,   79,   81,   27,  169,   82,   79,   48,   26,
 /*    70 */    26,   74,   18,   18,   18,   18,   18,   18,   41,   19,
 /*    80 */    25,    4,    4,   17,   17,   26,   74,   18,   18,   18,
 /*    90 */    18,   18,   18,   41,   19,   25,    4,    4,   17,   17,
 /*   100 */    74,   18,   18,   18,   18,   18,   18,   41,   19,   25,
 /*   110 */     4,    4,   17,   17,   24,  102,  139,   15,   39,   35,
 /*   120 */   144,  117,   29,  102,   16,   30,   28,   64,   60,   24,
 /*   130 */    97,    6,   37,  118,   52,    1,   32,   52,   57,  102,
 /*   140 */    93,  123,  102,   67,   99,  102,   36,   37,   62,   34,
 /*   150 */   102,   32,  119,  128,   66,   47,   84,  122,  148,  125,
 /*   160 */    77,  129,   24,   52,   29,   90,   40,   21,   23,    2,
 /*   170 */   102,  102,   16,   88,  142,   63,  102,   24,  102,    6,
 /*   180 */    64,  100,   33,   91,    2,  114,  234,   42,   93,  123,
 /*   190 */   102,  102,   48,  120,   33,   31,   56,   59,   22,  131,
 /*   200 */    61,  128,    2,   47,   22,  122,  148,  125,   77,  129,
 /*   210 */    24,  102,  102,   43,   27,  116,   31,  126,  127,   87,
 /*   220 */    16,  113,  139,  143,  102,   24,  102,    6,  137,  102,
 /*   230 */    73,  102,   13,   71,  135,   65,   93,  123,   11,  102,
 /*   240 */   102,  102,  102,   69,   70,   72,   68,  121,   10,  128,
 /*   250 */    96,   47,   54,  122,  148,  125,   77,  129,   24,  139,
 /*   260 */   146,  139,  145,   22,    8,   98,    9,  138,   16,   12,
 /*   270 */    53,    5,  106,   24,   14,    6,   83,   80,  115,  133,
 /*   280 */   107,   44,  132,  124,   93,  123,  101,   79,   89,  104,
 /*   290 */   134,  130,    3,    7,   46,   29,  141,  128,   45,   47,
 /*   300 */   147,  122,  148,  125,   77,  129,   92,   75,   94,   95,
 /*   310 */    76,  112,  111,  105,   52,   38,  140,   51,  136,   58,
 /*   320 */    55,   50,   20,  108,   49,  168,   85,
    ];
    public static $yy_lookahead = [
 /*     0 */     1,    2,    3,    4,    5,    6,    7,    8,    9,   10,
 /*    10 */    11,   12,   13,   14,   15,   16,    1,    2,    3,    4,
 /*    20 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*    30 */    15,   16,   55,   56,   57,   58,   21,   76,   39,   40,
 /*    40 */     1,    2,    3,    4,    5,    6,    7,    8,    9,   10,
 /*    50 */    11,   12,   13,   14,   15,   16,   13,   14,   15,   16,
 /*    60 */    26,   19,   28,   53,   22,   31,   36,   28,   58,    1,
 /*    70 */     2,    3,    4,    5,    6,    7,    8,    9,   10,   11,
 /*    80 */    12,   13,   14,   15,   16,    2,    3,    4,    5,    6,
 /*    90 */     7,    8,    9,   10,   11,   12,   13,   14,   15,   16,
 /*   100 */     3,    4,    5,    6,    7,    8,    9,   10,   11,   12,
 /*   110 */    13,   14,   15,   16,    3,   66,   55,   56,   57,   70,
 /*   120 */    29,   30,   20,   66,   13,   23,   24,   70,   71,   18,
 /*   130 */    81,   20,   66,   67,   58,   20,   70,   58,   63,   66,
 /*   140 */    29,   30,   66,   70,   29,   66,   70,   66,   67,   70,
 /*   150 */    66,   70,   79,   42,   70,   44,   45,   46,   47,   48,
 /*   160 */    49,   50,    3,   58,   20,   72,   10,   11,   12,   25,
 /*   170 */    66,   66,   13,   29,   70,   70,   66,   18,   66,   20,
 /*   180 */    70,   71,   70,   74,   25,   73,   52,   53,   29,   30,
 /*   190 */    66,   66,   58,   21,   70,   70,   64,   73,   26,   21,
 /*   200 */    75,   42,   25,   44,   26,   46,   47,   48,   49,   50,
 /*   210 */     3,   66,   66,   21,   22,   70,   70,   29,   30,   68,
 /*   220 */    13,   75,   55,   56,   66,   18,   66,   20,   70,   66,
 /*   230 */    70,   66,   34,   70,   27,   70,   29,   30,   26,   66,
 /*   240 */    66,   66,   66,   70,   70,   70,   70,   21,   34,   42,
 /*   250 */    41,   44,   42,   46,   47,   48,   49,   50,    3,   55,
 /*   260 */    56,   55,   56,   26,   20,   42,   26,   21,   13,   34,
 /*   270 */    21,   26,   21,   18,   26,   20,   42,   38,   37,   29,
 /*   280 */    65,   61,   66,   54,   29,   30,   69,   28,   35,   19,
 /*   290 */    69,   66,   20,   20,   20,   20,   54,   42,   66,   44,
 /*   300 */    69,   46,   47,   48,   49,   50,   43,   80,   80,   33,
 /*   310 */    80,   78,   69,   77,   58,   31,   69,   59,   66,   62,
 /*   320 */    60,   60,   32,   69,   59,   82,   64,
];
    const YY_SHIFT_USE_DFLT      = -2;
    const YY_SHIFT_MAX           = 97;
    public static $yy_shift_ofst = [
 /*     0 */   177,  144,  207,  159,  111,  207,  159,  159,  255,  255,
 /*    10 */   255,  255,  255,  255,  255,  275,  255,  255,  255,  255,
 /*    20 */   255,  255,  255,  255,  255,  255,  255,  102,  275,  177,
 /*    30 */   275,   -1,   39,   39,   68,   68,   68,   34,  115,   42,
 /*    40 */   273,  272,  270,  259,  253,  259,  177,  274,  284,  290,
 /*    50 */   239,  290,  284,  259,  263,  276,  209,  239,   30,   -2,
 /*    60 */    -2,   -2,   -2,   15,   68,   68,   68,   68,   83,   97,
 /*    70 */    97,   43,   43,   43,  156,  178,  172,  188,  192,   91,
 /*    80 */   198,  246,  241,  250,  234,  251,  248,  245,  249,  235,
 /*    90 */   240,  212,  223,  244,  237,  214,  210,  226,
];
    const YY_REDUCE_USE_DFLT      = -40;
    const YY_REDUCE_MAX           = 62;
    public static $yy_reduce_ofst = [
 /*     0 */   134,  -23,   81,   79,   73,   66,  105,   76,   49,  110,
 /*    10 */    57,  112,  124,  125,  146,   61,  145,  158,  160,  163,
 /*    20 */   165,  173,   84,  174,  104,  175,  176,  167,  204,   10,
 /*    30 */   206,  236,  247,  243,  230,  228,  227,  231,  232,  242,
 /*    40 */   216,  225,  229,  221,  257,  217,  256,  252,  265,  260,
 /*    50 */   262,  261,  258,  254,  233,  220,  215,  132,   75,  109,
 /*    60 */    93,  -39,  151,
];
    public static $yyExpectedTokens = [
        /* 0 */ [25],
        /* 1 */ [20, 25, 29],
        /* 2 */ [3, 13, 18, 20, 27, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 3 */ [3, 13, 18, 20, 25, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 4 */ [3, 13, 18, 20, 29, 30, 42, 44, 45, 46, 47, 48, 49, 50],
        /* 5 */ [3, 13, 18, 20, 27, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 6 */ [3, 13, 18, 20, 25, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 7 */ [3, 13, 18, 20, 25, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 8 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 9 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 10 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 11 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 12 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 13 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 14 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 15 */ [20],
        /* 16 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 17 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 18 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 19 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 20 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 21 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 22 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 23 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 24 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 25 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 26 */ [3, 13, 18, 20, 29, 30, 42, 44, 46, 47, 48, 49, 50],
        /* 27 */ [20, 23, 24],
        /* 28 */ [20],
        /* 29 */ [25],
        /* 30 */ [20],
        /* 31 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 39, 40],
        /* 32 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 28],
        /* 33 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 28],
        /* 34 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 35 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 36 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 37 */ [26, 28, 31],
        /* 38 */ [20, 29],
        /* 39 */ [19, 22],
        /* 40 */ [20],
        /* 41 */ [20],
        /* 42 */ [19],
        /* 43 */ [28],
        /* 44 */ [35],
        /* 45 */ [28],
        /* 46 */ [25],
        /* 47 */ [20],
        /* 48 */ [31],
        /* 49 */ [32],
        /* 50 */ [38],
        /* 51 */ [32],
        /* 52 */ [31],
        /* 53 */ [28],
        /* 54 */ [43],
        /* 55 */ [33],
        /* 56 */ [41],
        /* 57 */ [38],
        /* 58 */ [36],
        /* 59 */ [],
        /* 60 */ [],
        /* 61 */ [],
        /* 62 */ [],
        /* 63 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 21],
        /* 64 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 65 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 66 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 67 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 68 */ [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 69 */ [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 70 */ [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 71 */ [13, 14, 15, 16],
        /* 72 */ [13, 14, 15, 16],
        /* 73 */ [13, 14, 15, 16],
        /* 74 */ [10, 11, 12],
        /* 75 */ [21, 26],
        /* 76 */ [21, 26],
        /* 77 */ [29, 30],
        /* 78 */ [21, 22],
        /* 79 */ [29, 30],
        /* 80 */ [34],
        /* 81 */ [21],
        /* 82 */ [37],
        /* 83 */ [29],
        /* 84 */ [42],
        /* 85 */ [21],
        /* 86 */ [26],
        /* 87 */ [26],
        /* 88 */ [21],
        /* 89 */ [34],
        /* 90 */ [26],
        /* 91 */ [26],
        /* 92 */ [42],
        /* 93 */ [20],
        /* 94 */ [26],
        /* 95 */ [34],
        /* 96 */ [42],
        /* 97 */ [21],
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
        /* 138 */ [],
        /* 139 */ [],
        /* 140 */ [],
        /* 141 */ [],
        /* 142 */ [],
        /* 143 */ [],
        /* 144 */ [],
        /* 145 */ [],
        /* 146 */ [],
        /* 147 */ [],
        /* 148 */ [],
];
    public static $yy_default = [
 /*     0 */   233,  158,  233,  233,  233,  233,  233,  233,  230,  233,
 /*    10 */   233,  233,  233,  233,  233,  158,  233,  233,  233,  233,
 /*    20 */   233,  233,  233,  233,  233,  233,  233,  233,  233,  233,
 /*    30 */   233,  193,  169,  169,  232,  232,  232,  200,  233,  151,
 /*    40 */   233,  233,  151,  169,  182,  169,  233,  233,  233,  175,
 /*    50 */   189,  175,  233,  169,  199,  177,  197,  189,  187,  184,
 /*    60 */   179,  195,  163,  233,  180,  174,  231,  206,  203,  209,
 /*    70 */   211,  208,  210,  202,  233,  233,  233,  233,  233,  233,
 /*    80 */   233,  233,  233,  233,  233,  233,  188,  161,  233,  233,
 /*    90 */   176,  181,  233,  221,  229,  233,  233,  233,  198,  170,
 /*   100 */   178,  171,  200,  218,  150,  190,  160,  159,  173,  191,
 /*   110 */   192,  185,  196,  194,  183,  186,  216,  168,  162,  204,
 /*   120 */   212,  219,  220,  222,  149,  224,  225,  226,  227,  228,
 /*   130 */   214,  213,  215,  205,  172,  166,  201,  207,  152,  153,
 /*   140 */   165,  154,  217,  155,  167,  156,  157,  164,  223,
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
    const YYNOCODE      = 83;
    const YYSTACKDEPTH  = 100;
    const YYNSTATE      = 149;
    const YYNRULE       = 84;
    const YYERRORSYMBOL = 51;
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
  'LEFT_PAREN',    'RIGHT_PAREN',   'OP_UNION',      'OP_ALL',
  'OP_DISTINCT',   'SELECT',        'COMMA',         'COLUMN_STAR',
  'AS',            'LITERAL',       'QUOTED',        'FROM',
  'WHERE',         'SPLIT',         'BY',            'GROUP',
  'WITH',          'ROLLUP',        'ORDER',         'ASC',
  'DESC',          'LIMIT',         'NUMBER',        'OFFSET',
  'OP_EXISTS',     'INTERVAL',      'COLUMN',        'PLACEHOLDER',
  'VARIABLE',      'AT',            'NULL',          'error',
  'start',         'select_query_part',  'trailing_semicolon',  'select_paren',
  'select_union_paren',  'select_union',  'select_clause',  'from_clause',
  'where_clause',  'split_clause',  'group_clause',  'with_rollup_clause',
  'order_clause',  'limit_clause',  'select_subquery_part',  'select_field',
  'select_fields_extra',  'alias_optional',  'expression',    'split_expression',
  'split_expressions_extra',  'group_expression',  'group_expressions_extra',  'order_expression',
  'comma_order_expression_opt',  'direction_opt',  'limit_offset_opt',  'interval_expression',
  'comma_expressions_opt',  'func_args',
    ];

    /**
     * For tracing reduce actions, the names of all rules are required.
     *
     * @var array
     */
    public static $yyRuleName = [
 /*   0 */ 'start ::= select_query_part trailing_semicolon',
 /*   1 */ 'trailing_semicolon ::= SEMICOLON',
 /*   2 */ 'trailing_semicolon ::=',
 /*   3 */ 'select_paren ::= LEFT_PAREN select_query_part RIGHT_PAREN',
 /*   4 */ 'select_union_paren ::= select_paren',
 /*   5 */ 'select_union ::= select_union_paren select_union trailing_semicolon',
 /*   6 */ 'select_union ::= select_union OP_UNION select_union_paren',
 /*   7 */ 'select_union ::= select_union OP_UNION OP_ALL select_union_paren',
 /*   8 */ 'select_union ::= select_union OP_UNION OP_DISTINCT select_union_paren',
 /*   9 */ 'select_union ::=',
 /*  10 */ 'select_query_part ::= select_clause from_clause where_clause split_clause group_clause with_rollup_clause order_clause limit_clause',
 /*  11 */ 'select_subquery_part ::= LEFT_PAREN select_clause from_clause where_clause order_clause RIGHT_PAREN',
 /*  12 */ 'select_clause ::= SELECT select_field select_fields_extra',
 /*  13 */ 'select_fields_extra ::= select_fields_extra COMMA select_field',
 /*  14 */ 'select_fields_extra ::=',
 /*  15 */ 'select_field ::= select_subquery_part alias_optional',
 /*  16 */ 'select_field ::= expression alias_optional',
 /*  17 */ 'select_field ::= COLUMN_STAR',
 /*  18 */ 'alias_optional ::= AS LITERAL',
 /*  19 */ 'alias_optional ::= AS QUOTED',
 /*  20 */ 'alias_optional ::=',
 /*  21 */ 'from_clause ::= FROM LITERAL',
 /*  22 */ 'from_clause ::= FROM select_subquery_part alias_optional',
 /*  23 */ 'from_clause ::= FROM LEFT_PAREN select_union RIGHT_PAREN alias_optional',
 /*  24 */ 'from_clause ::= FROM LEFT_PAREN LITERAL RIGHT_PAREN alias_optional',
 /*  25 */ 'where_clause ::= WHERE expression',
 /*  26 */ 'where_clause ::=',
 /*  27 */ 'split_clause ::= SPLIT BY split_expression split_expressions_extra',
 /*  28 */ 'split_clause ::=',
 /*  29 */ 'split_expressions_extra ::= split_expressions_extra COMMA split_expression',
 /*  30 */ 'split_expressions_extra ::=',
 /*  31 */ 'split_expression ::= expression',
 /*  32 */ 'group_clause ::= GROUP BY group_expression group_expressions_extra',
 /*  33 */ 'group_clause ::=',
 /*  34 */ 'group_expressions_extra ::= group_expressions_extra COMMA group_expression',
 /*  35 */ 'group_expressions_extra ::=',
 /*  36 */ 'group_expression ::= expression alias_optional',
 /*  37 */ 'with_rollup_clause ::= WITH ROLLUP',
 /*  38 */ 'with_rollup_clause ::=',
 /*  39 */ 'order_clause ::= ORDER BY order_expression comma_order_expression_opt',
 /*  40 */ 'order_clause ::=',
 /*  41 */ 'order_expression ::= expression direction_opt',
 /*  42 */ 'direction_opt ::= ASC',
 /*  43 */ 'direction_opt ::= DESC',
 /*  44 */ 'direction_opt ::=',
 /*  45 */ 'comma_order_expression_opt ::= comma_order_expression_opt COMMA order_expression',
 /*  46 */ 'comma_order_expression_opt ::=',
 /*  47 */ 'limit_clause ::= LIMIT NUMBER limit_offset_opt',
 /*  48 */ 'limit_clause ::=',
 /*  49 */ 'limit_offset_opt ::= OFFSET NUMBER',
 /*  50 */ 'limit_offset_opt ::=',
 /*  51 */ 'expression ::= select_subquery_part',
 /*  52 */ 'expression ::= OP_EXISTS select_subquery_part',
 /*  53 */ 'expression ::= expression OP_EQ|OP_NE|OP_GT|OP_GTEQ|OP_LT|OP_LTEQ expression',
 /*  54 */ 'expression ::= expression OP_OR|OP_AND expression',
 /*  55 */ 'expression ::= expression OP_MINUS|OP_PLUS interval_expression',
 /*  56 */ 'interval_expression ::= INTERVAL NUMBER LITERAL',
 /*  57 */ 'interval_expression ::= expression',
 /*  58 */ 'expression ::= expression OP_MULTIPLY|OP_DIVIDE expression',
 /*  59 */ 'expression ::= expression OP_LIKE expression',
 /*  60 */ 'expression ::= expression OP_NOT OP_LIKE expression',
 /*  61 */ 'expression ::= expression OP_REGEXP expression',
 /*  62 */ 'expression ::= expression OP_NOT OP_REGEXP expression',
 /*  63 */ 'expression ::= expression OP_IN LEFT_PAREN expression comma_expressions_opt RIGHT_PAREN',
 /*  64 */ 'expression ::= expression OP_NOT OP_IN LEFT_PAREN expression comma_expressions_opt RIGHT_PAREN',
 /*  65 */ 'expression ::= expression OP_IN select_subquery_part',
 /*  66 */ 'expression ::= expression OP_NOT OP_IN select_subquery_part',
 /*  67 */ 'expression ::= OP_MINUS expression',
 /*  68 */ 'expression ::= OP_BANG|OP_NOT expression',
 /*  69 */ 'expression ::= LEFT_PAREN expression RIGHT_PAREN',
 /*  70 */ 'expression ::= LITERAL LEFT_PAREN func_args RIGHT_PAREN',
 /*  71 */ 'expression ::= COLUMN',
 /*  72 */ 'expression ::= LITERAL',
 /*  73 */ 'expression ::= QUOTED',
 /*  74 */ 'expression ::= PLACEHOLDER',
 /*  75 */ 'expression ::= VARIABLE',
 /*  76 */ 'expression ::= AT LITERAL',
 /*  77 */ 'expression ::= AT QUOTED',
 /*  78 */ 'expression ::= NUMBER',
 /*  79 */ 'expression ::= NULL',
 /*  80 */ 'func_args ::= expression comma_expressions_opt',
 /*  81 */ 'func_args ::=',
 /*  82 */ 'comma_expressions_opt ::= comma_expressions_opt COMMA expression',
 /*  83 */ 'comma_expressions_opt ::=',
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
  ['lhs' => 52, 'rhs' => 2],
  ['lhs' => 54, 'rhs' => 1],
  ['lhs' => 54, 'rhs' => 0],
  ['lhs' => 55, 'rhs' => 3],
  ['lhs' => 56, 'rhs' => 1],
  ['lhs' => 57, 'rhs' => 3],
  ['lhs' => 57, 'rhs' => 3],
  ['lhs' => 57, 'rhs' => 4],
  ['lhs' => 57, 'rhs' => 4],
  ['lhs' => 57, 'rhs' => 0],
  ['lhs' => 53, 'rhs' => 8],
  ['lhs' => 66, 'rhs' => 6],
  ['lhs' => 58, 'rhs' => 3],
  ['lhs' => 68, 'rhs' => 3],
  ['lhs' => 68, 'rhs' => 0],
  ['lhs' => 67, 'rhs' => 2],
  ['lhs' => 67, 'rhs' => 2],
  ['lhs' => 67, 'rhs' => 1],
  ['lhs' => 69, 'rhs' => 2],
  ['lhs' => 69, 'rhs' => 2],
  ['lhs' => 69, 'rhs' => 0],
  ['lhs' => 59, 'rhs' => 2],
  ['lhs' => 59, 'rhs' => 3],
  ['lhs' => 59, 'rhs' => 5],
  ['lhs' => 59, 'rhs' => 5],
  ['lhs' => 60, 'rhs' => 2],
  ['lhs' => 60, 'rhs' => 0],
  ['lhs' => 61, 'rhs' => 4],
  ['lhs' => 61, 'rhs' => 0],
  ['lhs' => 72, 'rhs' => 3],
  ['lhs' => 72, 'rhs' => 0],
  ['lhs' => 71, 'rhs' => 1],
  ['lhs' => 62, 'rhs' => 4],
  ['lhs' => 62, 'rhs' => 0],
  ['lhs' => 74, 'rhs' => 3],
  ['lhs' => 74, 'rhs' => 0],
  ['lhs' => 73, 'rhs' => 2],
  ['lhs' => 63, 'rhs' => 2],
  ['lhs' => 63, 'rhs' => 0],
  ['lhs' => 64, 'rhs' => 4],
  ['lhs' => 64, 'rhs' => 0],
  ['lhs' => 75, 'rhs' => 2],
  ['lhs' => 77, 'rhs' => 1],
  ['lhs' => 77, 'rhs' => 1],
  ['lhs' => 77, 'rhs' => 0],
  ['lhs' => 76, 'rhs' => 3],
  ['lhs' => 76, 'rhs' => 0],
  ['lhs' => 65, 'rhs' => 3],
  ['lhs' => 65, 'rhs' => 0],
  ['lhs' => 78, 'rhs' => 2],
  ['lhs' => 78, 'rhs' => 0],
  ['lhs' => 70, 'rhs' => 1],
  ['lhs' => 70, 'rhs' => 2],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 79, 'rhs' => 3],
  ['lhs' => 79, 'rhs' => 1],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 4],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 4],
  ['lhs' => 70, 'rhs' => 6],
  ['lhs' => 70, 'rhs' => 7],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 4],
  ['lhs' => 70, 'rhs' => 2],
  ['lhs' => 70, 'rhs' => 2],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 4],
  ['lhs' => 70, 'rhs' => 1],
  ['lhs' => 70, 'rhs' => 1],
  ['lhs' => 70, 'rhs' => 1],
  ['lhs' => 70, 'rhs' => 1],
  ['lhs' => 70, 'rhs' => 1],
  ['lhs' => 70, 'rhs' => 2],
  ['lhs' => 70, 'rhs' => 2],
  ['lhs' => 70, 'rhs' => 1],
  ['lhs' => 70, 'rhs' => 1],
  ['lhs' => 81, 'rhs' => 2],
  ['lhs' => 81, 'rhs' => 0],
  ['lhs' => 80, 'rhs' => 3],
  ['lhs' => 80, 'rhs' => 0],
    ];

    /**
     * The following table contains a mapping of reduce action to method name
     * that handles the reduction.
     *
     * If a rule is not set, it has no handler.
     */
    public static $yyReduceMap = [
        0  => 0,
        3  => 3,
        4  => 4,
        5  => 5,
        6  => 6,
        7  => 7,
        8  => 8,
        10 => 10,
        11 => 11,
        12 => 12,
        39 => 12,
        80 => 12,
        13 => 13,
        45 => 13,
        82 => 13,
        15 => 15,
        16 => 16,
        17 => 17,
        18 => 18,
        21 => 18,
        25 => 18,
        19 => 19,
        22 => 22,
        23 => 23,
        24 => 24,
        27 => 27,
        32 => 27,
        29 => 29,
        34 => 29,
        31 => 31,
        36 => 36,
        37 => 37,
        41 => 41,
        42 => 42,
        43 => 43,
        47 => 47,
        49 => 49,
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
        63 => 63,
        64 => 64,
        65 => 65,
        66 => 66,
        67 => 67,
        68 => 67,
        69 => 69,
        70 => 70,
        71 => 71,
        72 => 72,
        73 => 73,
        74 => 74,
        75 => 75,
        76 => 76,
        77 => 77,
        78 => 78,
        79 => 79,
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
        $this->_result = $this->yystack[$this->yyidx + -1]->minor;
    }
//line 1310 "Parser.php"
//line 102 "Parser.y"
    public function yy_r3()
    {
        $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
    }
//line 1315 "Parser.php"
//line 107 "Parser.y"
    public function yy_r4()
    {
        $this->yystack[$this->yyidx + 0]->minor->setIsUnionPart(true);

        $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
    }
//line 1322 "Parser.php"
//line 114 "Parser.y"
    public function yy_r5()
    {
        $this->_retvalue = [['ANY', $this->yystack[$this->yyidx + -2]->minor]];
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $this->_retvalue = array_merge($this->_retvalue, $this->yystack[$this->yyidx + -1]->minor);
        }
    }
//line 1330 "Parser.php"
//line 122 "Parser.y"
    public function yy_r6()
    {
        if (!$this->yystack[$this->yyidx + -2]->minor) {
            $this->_retvalue = [];
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
        }

        $this->_retvalue[] = ['DISTINCT', $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1341 "Parser.php"
//line 133 "Parser.y"
    public function yy_r7()
    {
        if (!$this->yystack[$this->yyidx + -3]->minor) {
            $this->_retvalue = [];
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor;
        }

        $this->_retvalue[] = ['ANY', $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1352 "Parser.php"
//line 144 "Parser.y"
    public function yy_r8()
    {
        if (!$this->yystack[$this->yyidx + -3]->minor) {
            $this->_retvalue = [];
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor;
        }

        $this->_retvalue[] = ['DISTINCT', $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1363 "Parser.php"
//line 158 "Parser.y"
    public function yy_r10()
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
//line 1392 "Parser.php"
//line 187 "Parser.y"
    public function yy_r11()
    {
        $q = $this->statementFactory->createSelectPart($this->yystack[$this->yyidx + -4]->minor, $this->yystack[$this->yyidx + -3]->minor);

        if ($this->yystack[$this->yyidx + -2]->minor) {
            $q->setWhere($this->yystack[$this->yyidx + -2]->minor);
        }
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $q->setOrderBy($this->yystack[$this->yyidx + -1]->minor);
        }

        $q->setIsSubQuery(true);

        $this->_retvalue = $q;
    }
//line 1408 "Parser.php"
//line 203 "Parser.y"
    public function yy_r12()
    {
        $this->_retvalue = [$this->yystack[$this->yyidx + -1]->minor];
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = array_merge($this->_retvalue, $this->yystack[$this->yyidx + 0]->minor);
        }
    }
//line 1416 "Parser.php"
//line 212 "Parser.y"
    public function yy_r13()
    {
        if (!$this->yystack[$this->yyidx + -2]->minor) {
            $this->_retvalue = [];
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
        }
        $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;
    }
//line 1426 "Parser.php"
//line 223 "Parser.y"
    public function yy_r15()
    {
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = $this->statementFactory->createAlias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1435 "Parser.php"
//line 232 "Parser.y"
    public function yy_r16()
    {
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = $this->statementFactory->createAlias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1444 "Parser.php"
//line 241 "Parser.y"
    public function yy_r17()
    {
        $this->_retvalue = $this->statementFactory->createColumnStar(explode('.', $this->yystack[$this->yyidx + 0]->minor));
    }
//line 1449 "Parser.php"
//line 248 "Parser.y"
    public function yy_r18()
    {
        $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
    }
//line 1454 "Parser.php"
//line 252 "Parser.y"
    public function yy_r19()
    {
        $this->_retvalue = $this->processQuoted($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1459 "Parser.php"
//line 265 "Parser.y"
    public function yy_r22()
    {
        $this->_retvalue = $this->statementFactory->createAlias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1464 "Parser.php"
//line 270 "Parser.y"
    public function yy_r23()
    {
        $this->_retvalue = $this->statementFactory->createAlias($this->statementFactory->createUnion($this->yystack[$this->yyidx + -2]->minor), $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1469 "Parser.php"
//line 275 "Parser.y"
    public function yy_r24()
    {
        $this->_retvalue = $this->statementFactory->createAlias($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1474 "Parser.php"
//line 289 "Parser.y"
    public function yy_r27()
    {
        $this->_retvalue = ($this->yystack[$this->yyidx + -1]->minor ? [$this->yystack[$this->yyidx + -1]->minor] : []);
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = array_merge($this->_retvalue, $this->yystack[$this->yyidx + 0]->minor);
        }
    }
//line 1482 "Parser.php"
//line 300 "Parser.y"
    public function yy_r29()
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
//line 1495 "Parser.php"
//line 316 "Parser.y"
    public function yy_r31()
    {
        if ($this->yystack[$this->yyidx + 0]->minor instanceof Statement\Part\NullValue) {
            $this->_retvalue = false;
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
        }
    }
//line 1504 "Parser.php"
//line 353 "Parser.y"
    public function yy_r36()
    {
        if ($this->yystack[$this->yyidx + -1]->minor instanceof Statement\Part\NullValue) {
            $this->_retvalue = false;
        } elseif ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = $this->statementFactory->createAlias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1515 "Parser.php"
//line 366 "Parser.y"
    public function yy_r37()
    {
        $this->_retvalue = true;
    }
//line 1520 "Parser.php"
//line 385 "Parser.y"
    public function yy_r41()
    {
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = $this->statementFactory->createOrderDir($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1529 "Parser.php"
//line 396 "Parser.y"
    public function yy_r42()
    {
        $this->_retvalue = 'ASC';
    }
//line 1534 "Parser.php"
//line 401 "Parser.y"
    public function yy_r43()
    {
        $this->_retvalue = 'DESC';
    }
//line 1539 "Parser.php"
//line 423 "Parser.y"
    public function yy_r47()
    {
        $this->_retvalue = ['limit' => intval($this->yystack[$this->yyidx + -1]->minor)];
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue['offset'] = $this->yystack[$this->yyidx + 0]->minor;
        }
    }
//line 1547 "Parser.php"
//line 434 "Parser.y"
    public function yy_r49()
    {
        $this->_retvalue = intval($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1552 "Parser.php"
//line 440 "Parser.y"
    public function yy_r51()
    {
        $this->_retvalue = $this->statementFactory->createSubSelect($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1557 "Parser.php"
//line 445 "Parser.y"
    public function yy_r52()
    {
        $this->_retvalue = $this->statementFactory->createExists($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1562 "Parser.php"
//line 450 "Parser.y"
    public function yy_r53()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = $this->statementFactory->createBinaryComparison($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1570 "Parser.php"
//line 458 "Parser.y"
    public function yy_r54()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = $this->statementFactory->createBinaryLogical($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1578 "Parser.php"
//line 466 "Parser.y"
    public function yy_r55()
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
//line 1591 "Parser.php"
//line 479 "Parser.y"
    public function yy_r56()
    {
        $this->_retvalue = ['interval', $this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1596 "Parser.php"
//line 484 "Parser.y"
    public function yy_r57()
    {
        $this->_retvalue = ['expression', $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1601 "Parser.php"
//line 489 "Parser.y"
    public function yy_r58()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = $this->statementFactory->createBinaryMath($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1609 "Parser.php"
//line 497 "Parser.y"
    public function yy_r59()
    {
        $this->_retvalue = $this->statementFactory->createLike($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1614 "Parser.php"
//line 502 "Parser.y"
    public function yy_r60()
    {
        $this->_retvalue = $this->statementFactory->createLike($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
//line 1619 "Parser.php"
//line 507 "Parser.y"
    public function yy_r61()
    {
        $this->_retvalue = $this->statementFactory->createRegExp($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1624 "Parser.php"
//line 512 "Parser.y"
    public function yy_r62()
    {
        $this->_retvalue = $this->statementFactory->createRegExp($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
//line 1629 "Parser.php"
//line 517 "Parser.y"
    public function yy_r63()
    {
        $values = [$this->yystack[$this->yyidx + -2]->minor];
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $values = array_merge($values, $this->yystack[$this->yyidx + -1]->minor);
        }
        $this->_retvalue = $this->statementFactory->createIn($this->yystack[$this->yyidx + -5]->minor, $values);
    }
//line 1638 "Parser.php"
//line 526 "Parser.y"
    public function yy_r64()
    {
        $values = [$this->yystack[$this->yyidx + -2]->minor];
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $values = array_merge($values, $this->yystack[$this->yyidx + -1]->minor);
        }
        $this->_retvalue = $this->statementFactory->createIn($this->yystack[$this->yyidx + -6]->minor, $values, false);
    }
//line 1647 "Parser.php"
//line 535 "Parser.y"
    public function yy_r65()
    {
        $this->_retvalue = $this->statementFactory->createInSubquery($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1652 "Parser.php"
//line 540 "Parser.y"
    public function yy_r66()
    {
        $this->_retvalue = $this->statementFactory->createInSubquery($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
//line 1657 "Parser.php"
//line 545 "Parser.y"
    public function yy_r67()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = $this->statementFactory->createUnaryOperator($token, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1665 "Parser.php"
//line 561 "Parser.y"
    public function yy_r69()
    {
        $this->_retvalue = $this->statementFactory->createParentheses($this->yystack[$this->yyidx + -1]->minor);
    }
//line 1670 "Parser.php"
//line 566 "Parser.y"
    public function yy_r70()
    {
        if (!$this->yystack[$this->yyidx + -1]->minor) {
            $this->yystack[$this->yyidx + -1]->minor = [];
        }
        $this->_retvalue = $this->statementFactory->createFunctionCall($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + -1]->minor);
    }
//line 1678 "Parser.php"
//line 574 "Parser.y"
    public function yy_r71()
    {
        $this->_retvalue = $this->statementFactory->createColumn(explode('.', $this->yystack[$this->yyidx + 0]->minor));
    }
//line 1683 "Parser.php"
//line 579 "Parser.y"
    public function yy_r72()
    {
        $this->_retvalue = $this->statementFactory->createStringPart($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1688 "Parser.php"
//line 584 "Parser.y"
    public function yy_r73()
    {
        $this->_retvalue = $this->statementFactory->createStringPart($this->processQuoted($this->yystack[$this->yyidx + 0]->minor));
    }
//line 1693 "Parser.php"
//line 589 "Parser.y"
    public function yy_r74()
    {
        $value           = substr($this->yystack[$this->yyidx + 0]->minor, 1, -1);
        $this->_retvalue = $this->statementFactory->createPlaceholder($value);
    }
//line 1699 "Parser.php"
//line 595 "Parser.y"
    public function yy_r75()
    {
        $value           = substr($this->yystack[$this->yyidx + 0]->minor, 2, -1);
        $this->_retvalue = $this->statementFactory->createVariable($value);
    }
//line 1705 "Parser.php"
//line 601 "Parser.y"
    public function yy_r76()
    {
        $this->_retvalue = $this->statementFactory->createAliasRef($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1710 "Parser.php"
//line 606 "Parser.y"
    public function yy_r77()
    {
        $this->_retvalue = $this->statementFactory->createAliasRef($this->processQuoted($this->yystack[$this->yyidx + 0]->minor));
    }
//line 1715 "Parser.php"
//line 611 "Parser.y"
    public function yy_r78()
    {
        $this->_retvalue = $this->statementFactory->createNumber($this->yystack[$this->yyidx + 0]->minor + 0);
    }
//line 1720 "Parser.php"
//line 616 "Parser.y"
    public function yy_r79()
    {
        $this->_retvalue = $this->statementFactory->createNullValue();
    }
//line 1725 "Parser.php"

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

    throw new DpqlException("Error parsing DPQL statement at line $this->line (got $TOKEN)");
//line 1841 "Parser.php"
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
