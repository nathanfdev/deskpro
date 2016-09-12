<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class BanIp extends AbstractEntityRepository
{
    /** @var array */
    protected $counts = [];

    /**
     * Get a list of IPs suitable for display.
     *
     * @param int    $from
     * @param int    $limit
     * @param string $search_phrase
     *
     * @return array
     */
    public function getList($from = 0, $limit = 20, $search_phrase = '')
    {
        $qb = $this->createQueryBuilder('bi');
        $qb
            ->setFirstResult($from)
            ->setMaxResults($limit);
        if ($search_phrase) {
            $qb->where($qb->expr()->like('bi.banned_ip', $qb->expr()->literal(sprintf('%%%s%%', $search_phrase))));
        }
        $list                         = $qb->getQuery()->getArrayResult();
        $this->counts[$search_phrase] = count($list);

        return $list;
    }

    /**
     * @param int    $per_page
     * @param string $search_phrase
     *
     * @return int
     */
    public function getPageCount($per_page = 20, $search_phrase = '')
    {
        return ceil($this->getCount($search_phrase) / $per_page);
    }

    public function getCount($search_phrase = '')
    {
        if (is_string($search_phrase) && isset($this->counts[$search_phrase])) {
            return $this->counts[$search_phrase];
        }

        $where  = '';
        $params = [];

        if (!empty($search_phrase)) {
            $where            = 'banned_ip LIKE :search';
            $params['search'] = '%'.$search_phrase.'%';
        }

        $count = App::getDb()->countWithPlaceholders('ban_ips', $where, $params);

        return $this->counts[$search_phrase] = (int) $count;
    }

    /**
     * @param $ip
     *
     * @return bool
     */
    public function isIpBanned($ip)
    {
        $banned = App::getDb()->fetchColumn(
            '
            SELECT banned_ip
            FROM ban_ips
            WHERE banned_ip = ?
            LIMIT 1
        ',
            [$ip]
        );

        return $banned ? true : false;
    }

    public function removeAll()
    {
        App::getDb()->executeQuery(sprintf('DELETE FROM %s', $this->getTableName()));
    }
}
