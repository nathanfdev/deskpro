<?php

namespace DeskPRO\Bundle\VoiceBundle\Reports;

use DeskPRO\Bundle\AppBundle\Entity\AbstractVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\VoiceBundle\Model\BillingSummary\DeskproBillingSummary;
use Doctrine\ORM\EntityManager;

/**
 * Class BillingSummary.
 */
class BillingSummary
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param AbstractVoiceAccount $voiceAccount
     * @param \DateTime            $startDate
     * @param \DateTime            $endDate
     *
     * @return DeskproBillingSummary
     */
    public function getDeskproBillingSummary(AbstractVoiceAccount $voiceAccount, \DateTime $startDate, \DateTime $endDate)
    {
        return new DeskproBillingSummary(
            $this->getCallsCount($voiceAccount, $startDate, $endDate),
            $this->getTotalPrice($voiceAccount, $startDate, $endDate)
        );
    }

    /**
     * @param AbstractVoiceAccount $voiceAccount
     * @param \DateTime            $startDate
     * @param \DateTime            $endDate
     *
     * @return int
     */
    public function getCallsCount(AbstractVoiceAccount $voiceAccount, \DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('COUNT(c.id)')
            ->from(VoicePhoneCall::class, 'c')
            ->leftJoin('c.number', 'n')
            ->where('c.account = :account OR n.account = :account')
            ->andWhere('c.dateCreated BETWEEN :start_date AND :end_date')
            ->setParameter('account', $voiceAccount)
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param AbstractVoiceAccount $voiceAccount
     * @param \DateTime            $startDate
     * @param \DateTime            $endDate
     *
     * @return float
     */
    public function getTotalPrice(AbstractVoiceAccount $voiceAccount, \DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('SUM(p.cost)')
            ->from(AbstractVoicePhoneCallParticipant::class, 'p')
            ->join('p.phoneCall', 'c')
            ->leftJoin('c.number', 'n')
            ->where('c.account = :account OR n.account = :account')
            ->andWhere('c.dateCreated BETWEEN :start_date AND :end_date')
            ->setParameter('account', $voiceAccount)
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}
