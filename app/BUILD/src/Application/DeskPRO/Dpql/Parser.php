<?php

/**
 * DeskPRO.
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
//line 167 "Parser.php"

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
    const T_DISPLAY        = 20;
    const T_TABLE          = 21;
    const T_BAR            = 22;
    const T_LINE           = 23;
    const T_PIE            = 24;
    const T_AREA           = 25;
    const T_COMMA          = 26;
    const T_SELECT         = 27;
    const T_COLUMN_STAR    = 28;
    const T_AS             = 29;
    const T_LITERAL        = 30;
    const T_QUOTED         = 31;
    const T_FROM           = 32;
    const T_WHERE          = 33;
    const T_SPLIT          = 34;
    const T_BY             = 35;
    const T_GROUP          = 36;
    const T_WITH           = 37;
    const T_ROLLUP         = 38;
    const T_ORDER          = 39;
    const T_ASC            = 40;
    const T_DESC           = 41;
    const T_LIMIT          = 42;
    const T_NUMBER         = 43;
    const T_OFFSET         = 44;
    const T_INTERVAL       = 45;
    const T_LEFT_PAREN     = 46;
    const T_RIGHT_PAREN    = 47;
    const T_COLUMN         = 48;
    const T_PLACEHOLDER    = 49;
    const T_VARIABLE       = 50;
    const T_AT             = 51;
    const T_NULL           = 52;
    const YY_NO_ACTION     = 203;
    const YY_ACCEPT_ACTION = 202;
    const YY_ERROR_ACTION  = 201;

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
    const YY_SZ_ACTTAB       = 248;
    public static $yy_action = [
 /*     0 */    18,   18,   59,   20,   20,   20,   20,   20,   20,   64,
 /*    10 */    30,   19,    2,    2,   26,   26,   18,   18,   59,   20,
 /*    20 */    20,   20,   20,   20,   20,   64,   30,   19,    2,    2,
 /*    30 */    26,   26,   93,   85,   87,   91,   83,  114,  115,  102,
 /*    40 */   103,   18,   18,   59,   20,   20,   20,   20,   20,   20,
 /*    50 */    64,   30,   19,    2,    2,   26,   26,    2,    2,   26,
 /*    60 */    26,   80,  108,   71,   17,   28,   99,  202,   33,   61,
 /*    70 */    35,   18,   18,   59,   20,   20,   20,   20,   20,   20,
 /*    80 */    64,   30,   19,    2,    2,   26,   26,   18,   59,   20,
 /*    90 */    20,   20,   20,   20,   20,   64,   30,   19,    2,    2,
 /*   100 */    26,   26,   59,   20,   20,   20,   20,   20,   20,   64,
 /*   110 */    30,   19,    2,    2,   26,   26,   25,   51,   11,   90,
 /*   120 */    47,   22,   12,   77,  121,   52,   24,   89,   11,   12,
 /*   130 */    25,   25,  110,   95,   44,  119,   51,   70,   46,   15,
 /*   140 */    24,   82,  105,   74,   94,   25,   13,   22,   13,   31,
 /*   150 */   123,   79,   45,   21,    5,  100,  116,   74,   94,   23,
 /*   160 */     6,  109,  112,  113,   63,  117,   96,   43,  118,    3,
 /*   170 */   116,    4,   66,   23,   25,  109,  112,  113,   63,  117,
 /*   180 */    92,   22,    7,   10,   24,   98,   29,    9,    8,   25,
 /*   190 */   122,   81,  120,   48,   67,   65,   78,  111,   97,  124,
 /*   200 */    56,   74,   94,   34,   32,   14,   60,   73,   54,   50,
 /*   210 */    42,   88,  104,   62,  116,  107,   76,   23,   68,  109,
 /*   220 */   112,  113,   63,  117,   27,   69,   72,   49,   16,   57,
 /*   230 */    58,   39,   38,   53,   55,   37,   86,   84,  106,   75,
 /*   240 */     1,   36,   40,  161,  161,  101,  161,   41,
    ];
    public static $yy_lookahead = [
 /*     0 */     1,    2,    3,    4,    5,    6,    7,    8,    9,   10,
 /*    10 */    11,   12,   13,   14,   15,   16,    1,    2,    3,    4,
 /*    20 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*    30 */    15,   16,   21,   22,   23,   24,   25,   30,   31,   40,
 /*    40 */    41,    1,    2,    3,    4,    5,    6,    7,    8,    9,
 /*    50 */    10,   11,   12,   13,   14,   15,   16,   13,   14,   15,
 /*    60 */    16,   77,   47,   10,   11,   12,   79,   54,   55,   29,
 /*    70 */    57,    1,    2,    3,    4,    5,    6,    7,    8,    9,
 /*    80 */    10,   11,   12,   13,   14,   15,   16,    2,    3,    4,
 /*    90 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*   100 */    15,   16,    3,    4,    5,    6,    7,    8,    9,   10,
 /*   110 */    11,   12,   13,   14,   15,   16,    3,   70,   70,   72,
 /*   120 */    68,   26,   70,   69,   76,   70,   13,   68,   70,   70,
 /*   130 */     3,   18,   30,   31,   76,   80,   70,   75,   72,   70,
 /*   140 */    13,   28,   47,   30,   31,   18,   70,   26,   70,   20,
 /*   150 */    74,   82,   74,   46,   46,   43,   43,   30,   31,   46,
 /*   160 */    26,   48,   49,   50,   51,   52,   47,   43,   47,   26,
 /*   170 */    43,   35,   45,   46,    3,   48,   49,   50,   51,   52,
 /*   180 */    30,   26,   26,   35,   13,   65,   46,   26,   35,   18,
 /*   190 */    38,   44,   30,   70,   43,   73,   42,   56,   19,   66,
 /*   200 */    70,   30,   31,   66,   26,   70,   81,   81,   70,   70,
 /*   210 */    64,   67,   70,   81,   43,   70,   39,   46,   37,   48,
 /*   220 */    49,   50,   51,   52,   33,   36,   34,   70,   70,   70,
 /*   230 */    70,   61,   60,   70,   70,   59,   71,   71,   70,   32,
 /*   240 */    27,   58,   62,   83,   83,   78,   83,   63,
];
    const YY_SHIFT_USE_DFLT      = -2;
    const YY_SHIFT_MAX           = 81;
    public static $yy_shift_ofst = [
 /*     0 */   129,  113,  127,  113,  171,  171,  171,  171,  171,  171,
 /*    10 */   171,   -1,   40,   40,   70,   70,   70,  171,  171,  171,
 /*    20 */   171,  171,  171,  171,  171,  171,  171,  171,  171,  171,
 /*    30 */   171,   11,   11,  179,  178,  213,  207,  191,  192,  189,
 /*    40 */   181,  177,  154,  147,   -2,   -2,   -2,   -2,   15,   70,
 /*    50 */    70,   70,   70,   85,   99,   99,   44,   44,   44,   53,
 /*    60 */    95,  102,  121,    7,  107,  156,  151,  162,  152,  153,
 /*    70 */   161,  140,  148,  155,  108,  150,  136,  143,  124,  119,
 /*    80 */   134,  112,
];
    const YY_REDUCE_USE_DFLT      = -17;
    const YY_REDUCE_MAX           = 47;
    public static $yy_reduce_ofst = [
 /*     0 */    13,   52,   55,   59,   58,   69,   48,   47,   78,   76,
 /*    10 */    66,  167,  166,  165,  132,  126,  125,  164,  163,  160,
 /*    20 */   159,  158,  157,  123,  168,  145,  142,  139,  138,  135,
 /*    30 */   130,  137,  133,  141,  144,  183,  176,  172,  170,  180,
 /*    40 */   184,  146,  120,  -13,  -16,   62,  122,   54,
];
    public static $yyExpectedTokens = [
        /* 0 */ [20],
        /* 1 */ [3, 13, 18, 28, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 2 */ [3, 13, 18, 30, 31, 43, 45, 46, 48, 49, 50, 51, 52],
        /* 3 */ [3, 13, 18, 28, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 4 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 5 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 6 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 7 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 8 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 9 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 10 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 11 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 40, 41],
        /* 12 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 29],
        /* 13 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 29],
        /* 14 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 15 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 16 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 17 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 18 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 19 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 20 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 21 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 22 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 23 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 24 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 25 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 26 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 27 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 28 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 29 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 30 */ [3, 13, 18, 30, 31, 43, 46, 48, 49, 50, 51, 52],
        /* 31 */ [21, 22, 23, 24, 25],
        /* 32 */ [21, 22, 23, 24, 25],
        /* 33 */ [19],
        /* 34 */ [26],
        /* 35 */ [27],
        /* 36 */ [32],
        /* 37 */ [33],
        /* 38 */ [34],
        /* 39 */ [36],
        /* 40 */ [37],
        /* 41 */ [39],
        /* 42 */ [42],
        /* 43 */ [44],
        /* 44 */ [],
        /* 45 */ [],
        /* 46 */ [],
        /* 47 */ [],
        /* 48 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 47],
        /* 49 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 50 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 51 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 52 */ [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 53 */ [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 54 */ [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 55 */ [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        /* 56 */ [13, 14, 15, 16],
        /* 57 */ [13, 14, 15, 16],
        /* 58 */ [13, 14, 15, 16],
        /* 59 */ [10, 11, 12],
        /* 60 */ [26, 47],
        /* 61 */ [30, 31],
        /* 62 */ [26, 47],
        /* 63 */ [30, 31],
        /* 64 */ [46],
        /* 65 */ [26],
        /* 66 */ [43],
        /* 67 */ [30],
        /* 68 */ [38],
        /* 69 */ [35],
        /* 70 */ [26],
        /* 71 */ [46],
        /* 72 */ [35],
        /* 73 */ [26],
        /* 74 */ [46],
        /* 75 */ [30],
        /* 76 */ [35],
        /* 77 */ [26],
        /* 78 */ [43],
        /* 79 */ [47],
        /* 80 */ [26],
        /* 81 */ [43],
        /* 82 */ [],
        /* 83 */ [],
        /* 84 */ [],
        /* 85 */ [],
        /* 86 */ [],
        /* 87 */ [],
        /* 88 */ [],
        /* 89 */ [],
        /* 90 */ [],
        /* 91 */ [],
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
];
    public static $yy_default = [
 /*     0 */   201,  201,  201,  201,  201,  198,  201,  201,  201,  201,
 /*    10 */   201,  165,  144,  144,  200,  200,  200,  201,  201,  201,
 /*    20 */   201,  201,  201,  201,  201,  201,  201,  201,  201,  201,
 /*    30 */   201,  201,  201,  127,  136,  201,  201,  147,  149,  154,
 /*    40 */   159,  161,  169,  171,  167,  156,  151,  139,  201,  199,
 /*    50 */   146,  152,  176,  173,  181,  179,  178,  172,  180,  201,
 /*    60 */   201,  201,  201,  201,  201,  148,  201,  201,  201,  201,
 /*    70 */   153,  201,  201,  197,  189,  201,  201,  137,  201,  201,
 /*    80 */   160,  201,  141,  134,  140,  131,  157,  132,  129,  138,
 /*    90 */   150,  133,  145,  130,  190,  143,  187,  126,  128,  168,
 /*   100 */   170,  162,  163,  164,  177,  182,  184,  185,  186,  188,
 /*   110 */   142,  125,  191,  192,  193,  194,  195,  196,  183,  174,
 /*   120 */   175,  166,  158,  155,  135,
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
    const YYNOCODE      = 84;
    const YYSTACKDEPTH  = 100;
    const YYNSTATE      = 125;
    const YYNRULE       = 76;
    const YYERRORSYMBOL = 53;
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
  'DISPLAY',       'TABLE',         'BAR',           'LINE',
  'PIE',           'AREA',          'COMMA',         'SELECT',
  'COLUMN_STAR',   'AS',            'LITERAL',       'QUOTED',
  'FROM',          'WHERE',         'SPLIT',         'BY',
  'GROUP',         'WITH',          'ROLLUP',        'ORDER',
  'ASC',           'DESC',          'LIMIT',         'NUMBER',
  'OFFSET',        'INTERVAL',      'LEFT_PAREN',    'RIGHT_PAREN',
  'COLUMN',        'PLACEHOLDER',   'VARIABLE',      'AT',
  'NULL',          'error',         'start',         'display_query',
  'trailing_semicolon',  'display_clause',  'select_clause',  'from_clause',
  'where_clause',  'split_clause',  'group_clause',  'with_rollup_clause',
  'order_clause',  'limit_clause',  'display_type',  'display_type_option',
  'select_field',  'select_fields_extra',  'expression',    'alias_optional',
  'split_expression',  'split_expressions_extra',  'group_expression',  'group_expressions_extra',
  'order_expression',  'comma_order_expression_opt',  'direction_opt',  'limit_offset_opt',
  'interval_expression',  'comma_expressions_opt',  'func_args',
    ];

    /**
     * For tracing reduce actions, the names of all rules are required.
     *
     * @var array
     */
    public static $yyRuleName = [
 /*   0 */ 'start ::= display_query trailing_semicolon',
 /*   1 */ 'trailing_semicolon ::= SEMICOLON',
 /*   2 */ 'trailing_semicolon ::=',
 /*   3 */ 'display_query ::= display_clause select_clause from_clause where_clause split_clause group_clause with_rollup_clause order_clause limit_clause',
 /*   4 */ 'display_clause ::= DISPLAY display_type display_type_option',
 /*   5 */ 'display_type ::= TABLE',
 /*   6 */ 'display_type ::= BAR',
 /*   7 */ 'display_type ::= LINE',
 /*   8 */ 'display_type ::= PIE',
 /*   9 */ 'display_type ::= AREA',
 /*  10 */ 'display_type_option ::= COMMA display_type',
 /*  11 */ 'display_type_option ::=',
 /*  12 */ 'select_clause ::= SELECT select_field select_fields_extra',
 /*  13 */ 'select_fields_extra ::= select_fields_extra COMMA select_field',
 /*  14 */ 'select_fields_extra ::=',
 /*  15 */ 'select_field ::= expression alias_optional',
 /*  16 */ 'select_field ::= COLUMN_STAR',
 /*  17 */ 'alias_optional ::= AS LITERAL',
 /*  18 */ 'alias_optional ::= AS QUOTED',
 /*  19 */ 'alias_optional ::=',
 /*  20 */ 'from_clause ::= FROM LITERAL',
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
 /*  47 */ 'expression ::= expression OP_EQ|OP_NE|OP_GT|OP_GTEQ|OP_LT|OP_LTEQ expression',
 /*  48 */ 'expression ::= expression OP_OR|OP_AND expression',
 /*  49 */ 'expression ::= expression OP_MINUS|OP_PLUS interval_expression',
 /*  50 */ 'interval_expression ::= INTERVAL NUMBER LITERAL',
 /*  51 */ 'interval_expression ::= expression',
 /*  52 */ 'expression ::= expression OP_MULTIPLY|OP_DIVIDE expression',
 /*  53 */ 'expression ::= expression OP_LIKE expression',
 /*  54 */ 'expression ::= expression OP_NOT OP_LIKE expression',
 /*  55 */ 'expression ::= expression OP_REGEXP expression',
 /*  56 */ 'expression ::= expression OP_NOT OP_REGEXP expression',
 /*  57 */ 'expression ::= expression OP_IN LEFT_PAREN expression comma_expressions_opt RIGHT_PAREN',
 /*  58 */ 'expression ::= expression OP_NOT OP_IN LEFT_PAREN expression comma_expressions_opt RIGHT_PAREN',
 /*  59 */ 'expression ::= OP_MINUS expression',
 /*  60 */ 'expression ::= OP_BANG|OP_NOT expression',
 /*  61 */ 'expression ::= LEFT_PAREN expression RIGHT_PAREN',
 /*  62 */ 'expression ::= LITERAL LEFT_PAREN func_args RIGHT_PAREN',
 /*  63 */ 'expression ::= COLUMN',
 /*  64 */ 'expression ::= LITERAL',
 /*  65 */ 'expression ::= QUOTED',
 /*  66 */ 'expression ::= PLACEHOLDER',
 /*  67 */ 'expression ::= VARIABLE',
 /*  68 */ 'expression ::= AT LITERAL',
 /*  69 */ 'expression ::= AT QUOTED',
 /*  70 */ 'expression ::= NUMBER',
 /*  71 */ 'expression ::= NULL',
 /*  72 */ 'func_args ::= expression comma_expressions_opt',
 /*  73 */ 'func_args ::=',
 /*  74 */ 'comma_expressions_opt ::= comma_expressions_opt COMMA expression',
 /*  75 */ 'comma_expressions_opt ::=',
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
  ['lhs' => 54, 'rhs' => 2],
  ['lhs' => 56, 'rhs' => 1],
  ['lhs' => 56, 'rhs' => 0],
  ['lhs' => 55, 'rhs' => 9],
  ['lhs' => 57, 'rhs' => 3],
  ['lhs' => 66, 'rhs' => 1],
  ['lhs' => 66, 'rhs' => 1],
  ['lhs' => 66, 'rhs' => 1],
  ['lhs' => 66, 'rhs' => 1],
  ['lhs' => 66, 'rhs' => 1],
  ['lhs' => 67, 'rhs' => 2],
  ['lhs' => 67, 'rhs' => 0],
  ['lhs' => 58, 'rhs' => 3],
  ['lhs' => 69, 'rhs' => 3],
  ['lhs' => 69, 'rhs' => 0],
  ['lhs' => 68, 'rhs' => 2],
  ['lhs' => 68, 'rhs' => 1],
  ['lhs' => 71, 'rhs' => 2],
  ['lhs' => 71, 'rhs' => 2],
  ['lhs' => 71, 'rhs' => 0],
  ['lhs' => 59, 'rhs' => 2],
  ['lhs' => 60, 'rhs' => 2],
  ['lhs' => 60, 'rhs' => 0],
  ['lhs' => 61, 'rhs' => 4],
  ['lhs' => 61, 'rhs' => 0],
  ['lhs' => 73, 'rhs' => 3],
  ['lhs' => 73, 'rhs' => 0],
  ['lhs' => 72, 'rhs' => 1],
  ['lhs' => 62, 'rhs' => 4],
  ['lhs' => 62, 'rhs' => 0],
  ['lhs' => 75, 'rhs' => 3],
  ['lhs' => 75, 'rhs' => 0],
  ['lhs' => 74, 'rhs' => 2],
  ['lhs' => 63, 'rhs' => 2],
  ['lhs' => 63, 'rhs' => 0],
  ['lhs' => 64, 'rhs' => 4],
  ['lhs' => 64, 'rhs' => 0],
  ['lhs' => 76, 'rhs' => 2],
  ['lhs' => 78, 'rhs' => 1],
  ['lhs' => 78, 'rhs' => 1],
  ['lhs' => 78, 'rhs' => 0],
  ['lhs' => 77, 'rhs' => 3],
  ['lhs' => 77, 'rhs' => 0],
  ['lhs' => 65, 'rhs' => 3],
  ['lhs' => 65, 'rhs' => 0],
  ['lhs' => 79, 'rhs' => 2],
  ['lhs' => 79, 'rhs' => 0],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 80, 'rhs' => 3],
  ['lhs' => 80, 'rhs' => 1],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 4],
  ['lhs' => 70, 'rhs' => 3],
  ['lhs' => 70, 'rhs' => 4],
  ['lhs' => 70, 'rhs' => 6],
  ['lhs' => 70, 'rhs' => 7],
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
  ['lhs' => 82, 'rhs' => 2],
  ['lhs' => 82, 'rhs' => 0],
  ['lhs' => 81, 'rhs' => 3],
  ['lhs' => 81, 'rhs' => 0],
    ];

    /**
     * The following table contains a mapping of reduce action to method name
     * that handles the reduction.
     *
     * If a rule is not set, it has no handler.
     */
    public static $yyReduceMap = [
        3  => 3,
        4  => 4,
        5  => 5,
        6  => 6,
        7  => 7,
        8  => 8,
        9  => 9,
        10 => 10,
        17 => 10,
        20 => 10,
        21 => 10,
        12 => 12,
        35 => 12,
        72 => 12,
        13 => 13,
        41 => 13,
        74 => 13,
        15 => 15,
        16 => 16,
        18 => 18,
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
        60 => 59,
        61 => 61,
        62 => 62,
        63 => 63,
        64 => 64,
        65 => 65,
        66 => 66,
        67 => 67,
        68 => 68,
        69 => 69,
        70 => 70,
        71 => 71,
    ];
    /* Beginning here are the reduction cases.  A typical example
    ** follows:
    **  #line <lineno> <grammarfile>
    **   function yy_r0($yymsp){ ... }           // User supplied code
    **  #line <lineno> <thisfile>
    */
//line 90 "Parser.y"
    public function yy_r3()
    {
        $res = new Statement\Display($this->yystack[$this->yyidx + -8]->minor, $this->yystack[$this->yyidx + -7]->minor, $this->yystack[$this->yyidx + -6]->minor);

        if ($this->yystack[$this->yyidx + -5]->minor) {
            $res->setWhere($this->yystack[$this->yyidx + -5]->minor);
        }
        if ($this->yystack[$this->yyidx + -4]->minor) {
            $res->setSplitBy($this->yystack[$this->yyidx + -4]->minor);
        }
        if ($this->yystack[$this->yyidx + -3]->minor) {
            $res->setGroupBy($this->yystack[$this->yyidx + -3]->minor);
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
        if ($this->yystack[$this->yyidx + -2]->minor) {
            $res->setWithRollup(true);
        }

        $this->_result = $res;
    }
//line 1251 "Parser.php"
//line 120 "Parser.y"
    public function yy_r4()
    {
        $this->_retvalue = [$this->yystack[$this->yyidx + -1]->minor];
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;
        }
    }
//line 1259 "Parser.php"
//line 128 "Parser.y"
    public function yy_r5()
    {
        $this->_retvalue = 'table';
    }
//line 1264 "Parser.php"
//line 132 "Parser.y"
    public function yy_r6()
    {
        $this->_retvalue = 'bar';
    }
//line 1269 "Parser.php"
//line 136 "Parser.y"
    public function yy_r7()
    {
        $this->_retvalue = 'line';
    }
//line 1274 "Parser.php"
//line 140 "Parser.y"
    public function yy_r8()
    {
        $this->_retvalue = 'pie';
    }
//line 1279 "Parser.php"
//line 144 "Parser.y"
    public function yy_r9()
    {
        $this->_retvalue = 'area';
    }
//line 1284 "Parser.php"
//line 149 "Parser.y"
    public function yy_r10()
    {
        $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
    }
//line 1289 "Parser.php"
//line 156 "Parser.y"
    public function yy_r12()
    {
        $this->_retvalue = [$this->yystack[$this->yyidx + -1]->minor];
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = array_merge($this->_retvalue, $this->yystack[$this->yyidx + 0]->minor);
        }
    }
//line 1297 "Parser.php"
//line 165 "Parser.y"
    public function yy_r13()
    {
        if (!$this->yystack[$this->yyidx + -2]->minor) {
            $this->_retvalue = [];
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
        }
        $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;
    }
//line 1307 "Parser.php"
//line 178 "Parser.y"
    public function yy_r15()
    {
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = new Statement\Part\Alias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1316 "Parser.php"
//line 187 "Parser.y"
    public function yy_r16()
    {
        $this->_retvalue = new Statement\Part\ColumnStar(explode('.', $this->yystack[$this->yyidx + 0]->minor));
    }
//line 1321 "Parser.php"
//line 198 "Parser.y"
    public function yy_r18()
    {
        $this->_retvalue = $this->processQuoted($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1326 "Parser.php"
//line 221 "Parser.y"
    public function yy_r23()
    {
        $this->_retvalue = ($this->yystack[$this->yyidx + -1]->minor ? [$this->yystack[$this->yyidx + -1]->minor] : []);
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = array_merge($this->_retvalue, $this->yystack[$this->yyidx + 0]->minor);
        }
    }
//line 1334 "Parser.php"
//line 232 "Parser.y"
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
//line 1347 "Parser.php"
//line 248 "Parser.y"
    public function yy_r27()
    {
        if ($this->yystack[$this->yyidx + 0]->minor instanceof Statement\Part\NullValue) {
            $this->_retvalue = false;
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
        }
    }
//line 1356 "Parser.php"
//line 285 "Parser.y"
    public function yy_r32()
    {
        if ($this->yystack[$this->yyidx + -1]->minor instanceof Statement\Part\NullValue) {
            $this->_retvalue = false;
        } elseif ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = new Statement\Part\Alias($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1367 "Parser.php"
//line 298 "Parser.y"
    public function yy_r33()
    {
        $this->_retvalue = true;
    }
//line 1372 "Parser.php"
//line 317 "Parser.y"
    public function yy_r37()
    {
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue = new Statement\Part\OrderDir($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);
        } else {
            $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
        }
    }
//line 1381 "Parser.php"
//line 328 "Parser.y"
    public function yy_r38()
    {
        $this->_retvalue = 'ASC';
    }
//line 1386 "Parser.php"
//line 333 "Parser.y"
    public function yy_r39()
    {
        $this->_retvalue = 'DESC';
    }
//line 1391 "Parser.php"
//line 355 "Parser.y"
    public function yy_r43()
    {
        $this->_retvalue = ['limit' => intval($this->yystack[$this->yyidx + -1]->minor)];
        if ($this->yystack[$this->yyidx + 0]->minor) {
            $this->_retvalue['offset'] = $this->yystack[$this->yyidx + 0]->minor;
        }
    }
//line 1399 "Parser.php"
//line 366 "Parser.y"
    public function yy_r45()
    {
        $this->_retvalue = intval($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1404 "Parser.php"
//line 374 "Parser.y"
    public function yy_r47()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = new Statement\Part\BinaryComparison($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1412 "Parser.php"
//line 382 "Parser.y"
    public function yy_r48()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = new Statement\Part\BinaryLogical($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1420 "Parser.php"
//line 390 "Parser.y"
    public function yy_r49()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token          = $this->yystack[$this->yyidx + -1]->major;
        $expression = $this->yystack[$this->yyidx + 0]->minor;

        if ($expression[0] == 'interval') {
            $this->_retvalue = new Statement\Part\BinaryInterval($token, $this->yystack[$this->yyidx + -2]->minor, $expression[1], $expression[2]);
        } else {
            $this->_retvalue = new Statement\Part\BinaryMath($token, $this->yystack[$this->yyidx + -2]->minor, $expression[1]);
        }
    }
//line 1433 "Parser.php"
//line 403 "Parser.y"
    public function yy_r50()
    {
        $this->_retvalue = ['interval', $this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1438 "Parser.php"
//line 408 "Parser.y"
    public function yy_r51()
    {
        $this->_retvalue = ['expression', $this->yystack[$this->yyidx + 0]->minor];
    }
//line 1443 "Parser.php"
//line 413 "Parser.y"
    public function yy_r52()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = new Statement\Part\BinaryMath($token, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1451 "Parser.php"
//line 421 "Parser.y"
    public function yy_r53()
    {
        $this->_retvalue = new Statement\Part\Like($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1456 "Parser.php"
//line 426 "Parser.y"
    public function yy_r54()
    {
        $this->_retvalue = new Statement\Part\Like($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
//line 1461 "Parser.php"
//line 431 "Parser.y"
    public function yy_r55()
    {
        $this->_retvalue = new Statement\Part\RegExp($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1466 "Parser.php"
//line 436 "Parser.y"
    public function yy_r56()
    {
        $this->_retvalue = new Statement\Part\RegExp($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + 0]->minor, false);
    }
//line 1471 "Parser.php"
//line 441 "Parser.y"
    public function yy_r57()
    {
        $values = [$this->yystack[$this->yyidx + -2]->minor];
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $values = array_merge($values, $this->yystack[$this->yyidx + -1]->minor);
        }
        $this->_retvalue = new Statement\Part\In($this->yystack[$this->yyidx + -5]->minor, $values);
    }
//line 1480 "Parser.php"
//line 450 "Parser.y"
    public function yy_r58()
    {
        $values = [$this->yystack[$this->yyidx + -2]->minor];
        if ($this->yystack[$this->yyidx + -1]->minor) {
            $values = array_merge($values, $this->yystack[$this->yyidx + -1]->minor);
        }
        $this->_retvalue = new Statement\Part\In($this->yystack[$this->yyidx + -6]->minor, $values, false);
    }
//line 1489 "Parser.php"
//line 459 "Parser.y"
    public function yy_r59()
    {
        // this line should be = @$this->yystack[$this->yyidx + -1]->minor, but due to a parser generator bug, doesn't work.
    $token = $this->yystack[$this->yyidx + -1]->major;

        $this->_retvalue = new Statement\Part\UnaryOperator($token, $this->yystack[$this->yyidx + 0]->minor);
    }
//line 1497 "Parser.php"
//line 475 "Parser.y"
    public function yy_r61()
    {
        $this->_retvalue = new Statement\Part\Parentheses($this->yystack[$this->yyidx + -1]->minor);
    }
//line 1502 "Parser.php"
//line 480 "Parser.y"
    public function yy_r62()
    {
        if (!$this->yystack[$this->yyidx + -1]->minor) {
            $this->yystack[$this->yyidx + -1]->minor = [];
        }
        $this->_retvalue = new Statement\Part\FunctionCall($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + -1]->minor);
    }
//line 1510 "Parser.php"
//line 488 "Parser.y"
    public function yy_r63()
    {
        $this->_retvalue = new Statement\Part\Column(explode('.', $this->yystack[$this->yyidx + 0]->minor));
    }
//line 1515 "Parser.php"
//line 493 "Parser.y"
    public function yy_r64()
    {
        $this->_retvalue = new Statement\Part\StringPart($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1520 "Parser.php"
//line 498 "Parser.y"
    public function yy_r65()
    {
        $this->_retvalue = new Statement\Part\StringPart($this->processQuoted($this->yystack[$this->yyidx + 0]->minor));
    }
//line 1525 "Parser.php"
//line 503 "Parser.y"
    public function yy_r66()
    {
        $value           = substr($this->yystack[$this->yyidx + 0]->minor, 1, -1);
        $this->_retvalue = new Statement\Part\Placeholder($value);
    }
//line 1531 "Parser.php"
//line 509 "Parser.y"
    public function yy_r67()
    {
        $value           = substr($this->yystack[$this->yyidx + 0]->minor, 2, -1);
        $this->_retvalue = new Statement\Part\Variable($value);
    }
//line 1537 "Parser.php"
//line 515 "Parser.y"
    public function yy_r68()
    {
        $this->_retvalue = new Statement\Part\AliasRef($this->yystack[$this->yyidx + 0]->minor);
    }
//line 1542 "Parser.php"
//line 520 "Parser.y"
    public function yy_r69()
    {
        $this->_retvalue = new Statement\Part\AliasRef($this->processQuoted($this->yystack[$this->yyidx + 0]->minor));
    }
//line 1547 "Parser.php"
//line 525 "Parser.y"
    public function yy_r70()
    {
        $this->_retvalue = new Statement\Part\Number($this->yystack[$this->yyidx + 0]->minor + 0);
    }
//line 1552 "Parser.php"
//line 530 "Parser.y"
    public function yy_r71()
    {
        $this->_retvalue = new Statement\Part\NullValue();
    }
//line 1557 "Parser.php"

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

    throw new Exception("Error parsing DPQL statement at line $this->line");
//line 1673 "Parser.php"
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
