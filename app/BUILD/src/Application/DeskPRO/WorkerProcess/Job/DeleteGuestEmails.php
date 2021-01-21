<?php



namespace Application\DeskPRO\WorkerProcess\Job;

use DeskPRO\Bundle\AppBundle\Entity\GuestEmail;

/**
 * Delete stored guests emails,
 */
class DeleteGuestEmails extends AbstractJob
{
    const DEFAULT_INTERVAL = 86400;

    public function run()
    {
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
