<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\RefGenerator;

use Orb\Util\DpStrings;
use Orb\Util\Strings;

class CustomRef implements RefGeneratorInterface
{
    /** @var array */
    public static $keywords = [
        'A'     => true,
        '#'     => true,
        '?'     => true,
        'YEAR'  => true,
        'MONTH' => true,
        'DAY'   => true,
        'HOUR'  => true,
        'MIN'   => true,
        'SEC'   => true,
    ];

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var string
     */
    protected $format_string = [];

    /**
     * @var array
     */
    protected $format = [];

    /**
     * @var int
     */
    protected $max_tries = 100;

    /**
     * How many digits to append to the end.
     *
     * @var int
     */
    protected $append_count = 0;

    /**
     * $format_string shold encase keywords in brakcets. For example:
     *     <A><A><A><A>-<#><#><#><#>-<A><A><A><A>.
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param $format_string
     */
    public function __construct(\Doctrine\ORM\EntityManager $em, $format_string, $append_count = 0)
    {
        $this->em            = $em;
        $this->db            = $em->getConnection();
        $this->append_count  = $append_count;
        $this->format_string = $format_string;

        //------------------------------
        // Parses format string into array(token, repeated)
        //------------------------------

        $format = [];
        $tok    = strtok($format_string, '<>');
        $parts  = [];
        while ($tok !== false) {
            $parts[] = $tok;
            $tok     = strtok('<>');
        }

        $last   = null;
        $repeat = 0;
        foreach ($parts as $p) {
            if ($last === null) {
                $last = $p;
            }

            if ($last == $p) {
                ++$repeat;
            } else {
                $format[] = [$last, $repeat];
                $last     = $p;
                $repeat   = 1;
            }
        }

        if ($last !== null) {
            $format[] = [$last, $repeat];
        }

        $this->format = $format;
    }

    /**
     * Is a word a keyword?
     *
     * @param string $word
     *
     * @return bool
     */
    public function isKeyword($word)
    {
        return isset(self::$keywords[$word]);
    }

    /**
     * @param string $class
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateReference($class)
    {
        $table = $this->em->getClassMetadata($class)->getTableName();
        $field = 'ref';

        $stmt  = $this->db->prepare("SELECT COUNT(*) FROM `$table` WHERE `$field` = ? LIMIT 1");
        $stmt2 = $this->db->prepare('SELECT COUNT(*) FROM `ref_reserve` WHERE `obj_type` = ? AND `ref` = ?');

        $attempt      = 0;
        $append_count = 0;

        // Get the last count used for this series of pattern,
        // Ex: Queries db for '2012-06-%' and we extract the trailing
        // nums to get the last number used, and the inc
        if ($this->append_count) {
            $ref_check = $this->generateRefString(null);
            $last      = $this->db->fetchColumn("
                SELECT `$field` AS ref
                FROM `$table`
                WHERE `$field` LIKE ?
                ORDER BY id DESC
                LIMIT 1
            ", ["$ref_check%"]);

            $m = null;
            if ($this->isRefMatch($last, $m)) {
                $append_count = (int) $m['count'];
            }

            if (!$append_count) {
                $append_count = 0;
            }
        }

        while (true) {
            do {
                ++$attempt;
                ++$append_count;

                if ($attempt > $this->max_tries) {
                    throw new RefGeneratorException("Cannot find unique ref after $attempt attempts with pattern {$this->format_string}. Aborting.");
                }

                if ($attempt > $this->max_tries - 5) {
                    // Last five allowed attempts, fallback to trying random nums at the end
                    $ref = $this->generateRefString($append_count.mt_rand(1000, 9999));
                } else {
                    $ref = $this->generateRefString($append_count);
                }

                $stmt->execute([$ref]);
                $count = $stmt->fetchColumn();

                $stmt2->execute([$table, $ref]);
                $count2 = $stmt2->fetchColumn();
            } while ($count > 0 || $count2 > 0);

            try {
                $this->db->insert('ref_reserve', [
                    'obj_type' => $table,
                    'ref'      => $ref,
                ]);
                break;
            } catch (\Exception $e) {
                // Try again..
            }
        }

        return $ref;
    }

    /**
     * Generate a new ref string.
     *
     * @param int $count
     */
    public function generateRefString($count = 1)
    {
        $ref = [];

        foreach ($this->format as $seg) {
            list($type, $length) = $seg;

            switch ($type) {
                case 'A':
                    $ref[] = DpStrings::random($length, Strings::CHARS_ALPHA_IU);
                    break;

                case '#':
                    $ref[] = DpStrings::random($length, Strings::CHARS_NUM);
                    break;

                case '?':
                    $ref[] = DpStrings::random($length, Strings::CHARS_ALPHANUM_IU);
                    break;

                case 'YEAR':
                    $ref[] = date('Y');
                    break;

                case 'MONTH':
                    $ref[] = date('m');
                    break;

                case 'DAY':
                    $ref[] = date('d');
                    break;

                case 'HOUR':
                    $ref[] = date('H');
                    break;

                case 'MIN':
                    $ref[] = date('i');
                    break;

                case 'SEC':
                    $ref[] = date('s');
                    break;

                default:
                    $ref[] = str_repeat($type, $length);
                    break;
            }
        }

        if ($count && $this->append_count) {
            $length = $this->append_count;
            $ref[]  = sprintf("%0{$length}d", $count);
        }

        $ref = implode('', $ref);

        if (strlen($ref) > 100) {
            $ref = substr($ref, 0, 100);
        }

        return $ref;
    }

    public function getRegexString()
    {
        $regex = ['('];

        foreach ($this->format as $seg) {
            list($type, $length) = $seg;

            switch ($type) {
                case 'A':
                    $regex[] = "([A-Z]{$length})";
                    break;

                case '#':
                    $regex[] = "([0-9]{$length})";
                    break;

                case '?':
                    $regex[] = "([0-9A-Z]{$length})";
                    break;

                case 'YEAR':
                    $regex[] = "(19|20)\d{2}";
                    break;

                case 'MONTH':
                    $regex[] = '(0[1-9]|1[012])';
                    break;

                case 'DAY':
                    $regex[] = "([0-2]?\d|3[01])";
                    break;

                case 'HOUR':
                    $regex[] = "([01]\d|2[0-3])";
                    break;

                case 'MIN':
                    $regex[] = "([0-5]\d)";
                    break;

                case 'SEC':
                    $regex[] = "([0-5]\d)";
                    break;

                default:
                    $regex[] = '('.preg_quote(str_repeat($type, $length), '#').')';
                    break;
            }
        }

        if ($this->append_count) {
            $regex[] = '(?P<count>[0-9]{'.$this->append_count.'})';
        }

        $regex[] = ')';

        return implode('', $regex);
    }

    /**
     * Check if a string is a valid ref format. This only checks
     * the format, no checking if it exists or anything like that.
     *
     * @param string $ref
     *
     * @return bool
     */
    public function isRefMatch($ref, &$m = null)
    {
        return preg_match('#^'.$this->getRegexString().'$#', $ref, $m);
    }

    /**
     * Try to find all refs in a body of text and return an array of
     * found matches.
     *
     * The order doesnt matter. But usually implementations will check refs
     * in the order they appear in the array. So if there is such thing as priority,
     * the first one should be the most likely match.
     *
     * @param $string
     *
     * @return string[]
     */
    public function extractRefs($string, $ldelim = '\b', $rdelim = '\b')
    {
        $m = null;
        if (preg_match_all('#('.$ldelim.')'.$this->getRegexString().'('.$rdelim.')#', $string, $m, \PREG_PATTERN_ORDER)) {
            return $m[2];
        }

        return [];
    }
}
