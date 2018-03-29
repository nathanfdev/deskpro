<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\RefGenerator;

use Orb\Util\DpStrings;
use Orb\Util\Strings;

class RandomRef implements RefGeneratorInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\DBAL
     */
    protected $db;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
        $this->db = $em->getConnection();
    }

    public function generateReference($class)
    {
        $table = $this->em->getClassMetadata($class)->getTableName();
        $field = 'ref';

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `$table` WHERE `$field` = ? LIMIT 1");

        do {
            $ref = DpStrings::random(4, Strings::CHARS_ALPHA_IU).'-'.DpStrings::random(4, Strings::CHARS_NUM).'-'.DpStrings::random(4, Strings::CHARS_ALPHA_IU);

            $stmt->execute([$ref]);
            $count = $stmt->fetchColumn();
        } while ($count > 0);

        return $ref;
    }

    /**
     * Check if a string is a valid ref format. This only checks
     * the format, no checking if it exists or anything like that.
     *
     * @param string $ref
     *
     * @return bool
     */
    public function isRefMatch($ref)
    {
        return preg_match('#^([A-Z0-9]{4}\-[A-Z0-9]{4}\-[A-Z0-9]{4})$#', $ref);
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
        if (preg_match_all('#('.$ldelim.')([A-Z0-9]{4}\-[A-Z0-9]{4}\-[A-Z0-9]{4})('.$rdelim.')#', $string, $m, \PREG_PATTERN_ORDER)) {
            return $m[2];
        }

        return [];
    }
}
