<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class SearchLog extends AbstractEntityRepository
{
    public function getRatedSearchesFor($object_type, $object_id, $structure = 'all')
    {
        $search_ids_to_rating = $this->getEntityManager()->getConnection()->fetchAllKeyValue('
            SELECT searchlog_id, rating
            FROM ratings
            WHERE object_type = ? AND object_id = ? AND searchlog_id IS NOT NULL
        ', [$object_type, $object_id]);

        $logs = $this->getByIds(array_keys($search_ids_to_rating));

        if (!$logs) {
            return [];
        }

        if ($structure == 'all') {
            return $logs;
        }

        if ($structure == 'grouped') {
            $ret = ['helpful' => [], 'unhelpful' => []];
            foreach ($logs as $l) {
                if ($search_ids_to_rating[$l['id']] >= 1) {
                    $ret['helpful'] = $l;
                } else {
                    $ret['unhelpful'] = $l;
                }
            }

            return $ret;
        } elseif ($structure == 'counted') {
            $ret = ['helpful' => [], 'unhelpful' => []];
            foreach ($logs as $l) {
                if ($search_ids_to_rating[$l['id']] >= 1) {
                    if (!isset($ret['helpful'][$l['query']])) {
                        $ret['helpful'][$l['query']] = 0;
                    }
                    ++$ret['helpful'][$l['query']];
                } else {
                    if (!isset($ret['unhelpful'][$l['query']])) {
                        $ret['unhelpful'][$l['query']] = 0;
                    }
                    ++$ret['unhelpful'][$l['query']];
                }
            }

            asort($ret['helpful'], \SORT_NUMERIC);
            asort($ret['unhelpful'], \SORT_NUMERIC);

            return $ret;
        }

        return;
    }

    public function popularSearchTerms($limit = 100)
    {
        return App::getDb()->fetchAll("
            SELECT COUNT(*) AS num_searches, num_results, query
            FROM searchlog
            GROUP BY query
            ORDER BY num_searches DESC, num_results ASC
            LIMIT $limit
        ");
    }

    public function popularSearchTermsLowHits($limit = 100, $max_hits = 0)
    {
        return App::getDb()->fetchAll("
            SELECT COUNT(*) AS num_searches, num_results, query
            FROM searchlog
            WHERE num_results <= $max_hits
            GROUP BY query
            ORDER BY num_results ASC, num_searches DESC
            LIMIT $limit
        ");
    }

    public function recentSearchTerms($limit = 100)
    {
        return App::getDb()->fetchAll("
            SELECT COUNT(*) AS num_searches, num_results, query
            FROM searchlog
            GROUP BY query
            ORDER BY id DESC
            LIMIT $limit
        ");
    }

    public function getByIds(array $ids, $keep_order = false, $maxAgeInSec = null)
    {
        if (!$ids) {
            return [];
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('sl')
            ->from('DeskPRO:SearchLog', 'sl')
            ->where('sl.id IN (:ids)')
            ->setParameter('ids', $ids);

        if (!$keep_order) {
            $qb->orderBy('sl.id', 'DESC');
        }

        if ($maxAgeInSec) {
            $qb
                ->andWhere('sl.date_created >= :minDate')
                ->setParameter('minDate', (new \DateTime())->modify(sprintf('%s seconds ago', $maxAgeInSec)));
        }

        return $qb->getQuery()->execute();
    }
}
