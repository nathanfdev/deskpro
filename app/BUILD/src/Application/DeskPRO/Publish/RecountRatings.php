<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Publish;

use Doctrine\ORM\EntityManager;

class RecountRatings
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var int
     */
    protected $batch_size = 1000;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param int $batch_size
     */
    public function setBatchSize($batch_size)
    {
        $this->batch_size = $batch_size;
    }

    /**
     * @return int
     */
    public function getIdMax()
    {
        static $id_max = null;

        if ($id_max === null) {
            $id_max = $this->em->getConnection()->fetchColumn('SELECT id FROM ratings ORDER BY id DESC LIMIT 1');
        }

        return $id_max;
    }

    /**
     * @return float
     */
    public function countBatches()
    {
        static $num_batches = null;

        if ($num_batches === null) {
            $num_batches = ceil($this->getIdMax() / $this->batch_size);
        }

        return $num_batches;
    }

    /**
     * @param callable $status_fn
     */
    public function recountAll($status_fn = null)
    {
        if (!$status_fn) {
            $status_fn = function ($status_type, array $info) {
            };
        }

        $pages = $this->countBatches();
        $status_fn('start', ['num_batches' => $pages, 'batch_size' => $this->batch_size]);

        for ($i = 1; $i <= $pages; ++$i) {
            $status_fn('batch_start', ['batch' => $i]);
            $this->recountBatch($i);
            $status_fn('batch_end', ['batch' => $i]);
        }

        $status_fn('end', $i);
    }

    /**
     * @param int $page
     */
    public function recountBatch($page)
    {
        $start = (($page - 1) * $this->batch_size) + 1;
        $end   = $page * $this->batch_size;

        $ratings = $this->em->getConnection()->fetchAll("
            SELECT object_id, object_type, rating
            FROM ratings
            WHERE id BETWEEN $start AND $end
        ");

        $update_set = [];
        foreach ($ratings as $rating) {
            $key = $rating['object_type'].$rating['object_id'];
            if (!isset($update_set[$key])) {
                $update_set[$key] = ['type' => $rating['object_type'], 'id' => $rating['object_id'], 'count' => 0, 'rating' => 0];
            }

            ++$update_set[$key]['count'];
            $update_set[$key]['rating'] += $rating['rating'];
        }

        $this->em->getConnection()->beginTransaction();
        try {
            foreach ($update_set as $set) {
                switch ($set['type']) {
                    case 'article':   $table = 'articles'; break;
                    case 'download':  $table = 'downloads'; break;
                    case 'news':      $table = 'news'; break;
                    case 'feedback':  $table = 'feedback'; break;
                    default: continue;
                }

                if ($page == 0) {
                    $this->em->getConnection()->executeUpdate("
                        UPDATE $table
                        SET total_rating = ?, num_ratings ?
                        WHERE id = ?
                    ", [$set['rating'], $set['count'], $set['id']]);
                } else {
                    $this->em->getConnection()->executeUpdate("
                        UPDATE $table
                        SET total_rating = total_rating + ?, num_ratings = num_ratings + ?
                        WHERE id = ?
                    ", [$set['rating'], $set['count'], $set['id']]);
                }
            }
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }
    }
}
