<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Sms\Detector;

use Application\DeskPRO\Entity\SmsAccount;
use Doctrine\ORM\EntityManager;

/**
 * Detect what SmsAccount entity is related to sms messages.
 */
class SmsAccountDetector
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param      $sms_account_id
     * @param null $to_number
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return SmsAccount|null
     */
    public function detect($sms_account_id, $to_number = null)
    {
        if ($sms_account_id) {
            return $this->em->getRepository('DeskPRO:SmsAccount')->find($sms_account_id);
        }

        $phone_number = $this->em->getRepository('DeskPRO:PhoneNumber')->findByNumber($to_number);

        if (!$phone_number) {
            return;
        }

        $query = $this->em->createQuery('
            SELECT a
            FROM DeskPRO:SmsAccount a
            WHERE a.phone_number = :found_phone_number
        ');

        $query->setParameter('found_phone_number', $phone_number);

        return $query->getOneOrNullResult();
    }
}
