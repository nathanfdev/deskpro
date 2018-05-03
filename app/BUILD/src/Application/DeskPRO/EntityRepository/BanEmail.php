<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Doctrine\ORM\Query;

class BanEmail extends AbstractEntityRepository
{
    /** @var array */
    protected $counts = [];

    /**
     * Get a list of emails suitable for display.
     */
    public function getList($from = 0, $limit = 20, $search_phrase = '', $wildcard = false)
    {
        $where  = '1';
        $params = [];

        if (!empty($search_phrase)) {
            $where .= ' AND banned_email LIKE :search';
            $params['search'] = '%'.str_replace('%', '\%', $search_phrase).'%';
        }

        if ($wildcard) {
            $where .= ' AND banned_email LIKE "%\%%"';
        }

        $list = App::getDb()->fetchAllCol(sprintf('
            SELECT banned_email
            FROM ban_emails
            WHERE %s
            ORDER BY banned_email ASC
            LIMIT %d, %d
        ', $where, $from, $limit), $params);
        $this->counts[$search_phrase] = count($list);

        return $list;
    }

    /**
     * @param int    $per_page
     * @param string $search_phrase
     * @param bool   $wildcard
     *
     * @return int
     */
    public function getPageCount($per_page = 20, $search_phrase = '', $wildcard = false)
    {
        return ceil($this->getCount($search_phrase, $wildcard) / $per_page);
    }

    public function getPatterns($reload = false)
    {
        static $list;

        if ($reload || !$list) {
            $list = App::getDb()->fetchAllCol('
                SELECT banned_email
                FROM ban_emails
                WHERE is_pattern = 1
                ORDER BY banned_email ASC
            ');
        }

        return $list;
    }

    /**
     * Check if an email address is banned.
     *
     * @param $email
     *
     * @return bool
     */
    public function isEmailBanned($email, &$match = null)
    {
        $email = strtolower(trim($email));

        $banned_email = App::getDb()->fetchColumn('
            SELECT banned_email
            FROM ban_emails
            WHERE banned_email = ?
        ', [$email]);

        if ($banned_email) {
            $match = $banned_email;

            return true;
        }

        $patterns = $this->getPatterns();
        foreach ($patterns as $pattern) {
            if (\Orb\Util\Strings::isStarMatch($pattern, $email)) {
                $match = $pattern;

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $search_phrase
     * @param bool   $wildcard
     *
     * @return int
     */
    public function getCount($search_phrase = '', $wildcard = false)
    {
        if (is_string($search_phrase) && isset($this->counts[$search_phrase])) {
            return $this->counts[$search_phrase];
        }

        $where  = '1';
        $params = [];

        if (!empty($search_phrase)) {
            $where .= ' AND banned_email LIKE :search';
            $params['search'] = '%'.str_replace('%', '\%', $search_phrase).'%';
        }

        if ($wildcard) {
            $where .= ' AND banned_email LIKE "%\%%"';
        }

        $count = App::getDb()->countWithPlaceholders('ban_emails', $where, $params);

        return $this->counts[$search_phrase] = (int) $count;
    }

    public function removeAll()
    {
        App::getDb()->executeQuery(sprintf('DELETE FROM %s', $this->getTableName()));
    }

    /**
     * complete list of email bans.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->_em->createQuery(
            'SELECT e.banned_email FROM DeskPRO:BanEmail e'
        )->execute([], Query::HYDRATE_SCALAR);
    }
}
