<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class WorkerJob extends AbstractEntityRepository
{
    /**
     * @return mixed
     */
    public function getAll()
    {
        return $this->getEntityManager()->createQuery('
            SELECT j
            FROM DeskPRO:WorkerJob j
            ORDER BY j.interval ASC
        ')->execute();
    }

    public function clearAllLogs()
    {
        App::getDb()->exec("DELETE FROM log_items WHERE log_name LIKE 'worker_job.%'");
    }
}
