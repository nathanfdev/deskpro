<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use DeskPRO\Component\Lock\PdoStore;
use Doctrine\DBAL\Connection;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\RetryTillSaveStore;

/**
 * Class PhoneCallLockHelper.
 */
class PhoneCallLockHelper
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int $callId
     *
     * @return \Symfony\Component\Lock\Lock
     */
    public function createPhoneLock($callId)
    {
        $store   = new RetryTillSaveStore(new PdoStore($this->connection));
        $factory = new Factory($store);

        return $factory->createLock('voice-phone-call-'.$callId, 30);
    }
}
