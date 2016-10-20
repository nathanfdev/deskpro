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

namespace DeskPRO\Bundle\AppBundle\Twilio;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioAvailableNumber;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioExistingNumber;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioPaginate;
use Doctrine\ORM\EntityManager;
use Twilio\Rest\Client;
use Twilio\Values;

/**
 * Class TwilioAdapter.
 */
class TwilioAdapter
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $cache = [];

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
     * @param VoiceAccount $account
     *
     * @return \Twilio\Rest\Api\V2010\AccountInstance|false
     */
    public function getAccount(VoiceAccount $account)
    {
        $accountSid = $account->getAccountSid();

        if (!isset($this->cache['account'][$accountSid])) {
            try {
                $client = new Client($accountSid, $account->getAuthToken());
                $value  = $client->getAccount()->fetch();
            } catch (\Exception $e) {
                $value = false;
            }

            $this->cache['account'][$accountSid] = $value;
        }

        return $this->cache['account'][$accountSid];
    }

    /**
     * @param VoiceAccount $account
     * @param string       $countryCode
     * @param string       $type
     * @param array        $options
     *
     * @return TwilioAvailableNumber[]
     */
    public function getAvailablePhoneNumbers(VoiceAccount $account, $countryCode, $type, array $options)
    {
        $accountSid = $account->getAccountSid();
        $numbers    = [];

        try {
            $client = new Client($accountSid, $account->getAuthToken());
            $result = $client->availablePhoneNumbers($countryCode)->$type->page($options);

            $excludeNumbers = $this->getAccountNumbersList($account);

            foreach ($result as $apiNumber) {
                $numbers[] = new TwilioAvailableNumber(
                    $apiNumber,
                    $account,
                    isset($excludeNumbers[$apiNumber->phoneNumber]),
                    $type
                );
            }
        } catch (\Exception $e) {
        }

        return $numbers;
    }

    /**
     * @param VoiceAccount $account
     * @param int          $pageNum
     *
     * @return TwilioPaginate
     */
    public function getExistingPhoneNumbers(VoiceAccount $account, $pageNum = 1)
    {
        $accountSid = $account->getAccountSid();

        try {
            $client = new Client($accountSid, $account->getAuthToken());
            $page   = $client->incomingPhoneNumbers->page([], Values::NONE, Values::NONE, $pageNum - 1);

            $excludeNumbers = $this->getAccountNumbersList($account);

            $numbers = [];
            foreach ($page as $apiNumber) {
                $numbers[] = new TwilioExistingNumber(
                    $apiNumber,
                    $account,
                    isset($excludeNumbers[$apiNumber->phoneNumber])
                );
            }

            return new TwilioPaginate($numbers, $pageNum, $page);
        } catch (\Exception $e) {
            return new TwilioPaginate([], $pageNum);
        }
    }

    /**
     * @param VoiceAccount $account
     *
     * @return string[]
     */
    protected function getAccountNumbersList(VoiceAccount $account)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('n.number')
            ->from(VoiceNumber::class, 'n')
            ->where('n.account = :account')
            ->setParameter('account', $account)
        ;

        $result  = $qb->getQuery()->getArrayResult();
        $numbers = [];

        foreach ($result as $number) {
            $numbers[] = $number['number'];
        }

        return array_fill_keys($numbers, true);
    }
}
