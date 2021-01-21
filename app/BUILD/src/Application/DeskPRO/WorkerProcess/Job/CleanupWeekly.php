<?php



namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Entity\GuestEmail;

class CleanupWeekly extends AbstractJob
{
    const DEFAULT_INTERVAL = 604800;

    public function run()
    {
        $this->doRun();
        App::getDb()->setIsolationDefault();
    }

    private function doRun()
    {
        $date = date('Y-m-d H:i:s', strtotime('-1 year'));

        $num = App::getDb()->executeUpdate('
            DELETE FROM login_log
            WHERE date_created < ?
        ', [$date]);

        if ($num) {
            $this->logStatus("Cleaned up $num old login logs");
        }

        //Clean Guest Emails Older than 7 days
        $date = new \DateTime();
        $date->modify('-7 days');

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $qb = $em->getRepository(GuestEmail::class)->createQueryBuilder('x')
            ->delete()
            ->where('x.createdAt <= :date')
            ->setParameter(':date', $date)
            ->getQuery()
            ->getResult();

        if ($qb > 0) {
            $this->logStatus('Removed '.$qb.' old guest emails');
        }
    }
}
