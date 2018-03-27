<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Sync\Syncer;

use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Sync\SyncerHelper;
use Application\DeskPRO\Usersource\Sync\SyncerInterface;

abstract class AbstractSyncer implements SyncerInterface
{
    /**
     * @var SyncerHelper
     */
    protected $helper;

    /**
     * @param SyncerHelper $helper
     */
    public function __construct(SyncerHelper $helper)
    {
        $this->helper = $helper;
    }

    public function supportsUsersource(Usersource $usersource)
    {
        return $this->supportsUsersourceAdapter($usersource->getSourceType());
    }
}
