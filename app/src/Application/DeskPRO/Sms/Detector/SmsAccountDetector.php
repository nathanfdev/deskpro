<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\DeskPRO\Sms\Detector;

use Application\DeskPRO\Entity\SmsAccount;
use Doctrine\ORM\EntityManager;

/**
 * Detect what SmsAccount entity is related to sms messages
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
     * @param                                         $sms_account_id
     * @param  null                                   $to_number
     * @return SmsAccount|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function detect($sms_account_id, $to_number = null)
    {
        if ($sms_account_id) {
            return $this->em->getRepository('DeskPRO:SmsAccount')->find($sms_account_id);
        }

        $phone_number = $this->em->getRepository('DeskPRO:PhoneNumber')->findByNumber($to_number);

        if (!$phone_number) {
            return null;
        }

        $query = $this->em->createQuery("
            SELECT a
            FROM DeskPRO:SmsAccount a
            WHERE a.phone_number = :found_phone_number
        ");

        $query->setParameter('found_phone_number', $phone_number);

        return $query->getOneOrNullResult();
    }
}
