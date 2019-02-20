<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;

/**
 * Class VoicePhoneCallRepository.
 */
class VoicePhoneCallRepository extends AbstractEntityRepository
{
    /**
     * @param $callSid
     *
     * @return VoicePhoneCall|null
     */
    public function findByParticipantSid($callSid)
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->select('c')
            ->leftJoin('c.participants', 'p')
            ->where('p.callSid = :call_sid')
            ->setParameter('call_sid', $callSid)
        ;

        $result = $qb->getQuery()->getResult();

        return array_shift($result);
    }
}
