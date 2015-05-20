<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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
 */

namespace Application\DeskPRO\Usersource\Sync;


use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Entity\UsersourceSyncLog;
use Application\DeskPRO\EntityRepository\UsersourceSyncLog as UsersourceSyncLogRepo;
use Orb\Validator\StringEmail;

/**
 * The SyncManager is an aggregate of all of the syncers, but it is itself a "master" syncer.
 */
class SyncManager implements SyncerInterface
{
    /**
     * @var SyncerInterface[]
     */
    protected $syncers;

    /**
     * @var UsersourceSyncLogRepo
     */
    private $repo;

    public function __construct(array $syncers, UsersourceSyncLogRepo $repo)
    {
        $this->syncers = $syncers;
        $this->repo = $repo;
    }

    /**
     * @param Usersource $usersource
     * @return SyncerInterface
     */
    public function getSyncerForUsersource(Usersource $usersource)
    {
        return $this->getSyncerForAdapterClass($usersource->getSourceType());
    }

    /**
     * @param string $usersource_adapter_class_name
     * @return SyncerInterface
     */
    public function getSyncerForAdapterClass($usersource_adapter_class_name)
    {
        foreach ($this->syncers as $syncer) {
            if ($syncer->supportsUsersourceAdapter($usersource_adapter_class_name)) {
                return $syncer;
            }
        }
    }

    public function refreshIdentity(Usersource $usersource, $identity_or_email)
    {
        if ($this->isSyncable($usersource) && $syncer = $this->getSyncerForUsersource($usersource)) {
            return $syncer->refreshIdentity($usersource, $identity_or_email);
        }

        return null;
    }

    public function refreshAll(Usersource $usersource, SyncCursor $cursor, callable $pause_check)
    {
        if ($this->isSyncable($usersource) && $syncer = $this->getSyncerForUsersource($usersource)) {
            return $syncer->refreshAll($usersource, $cursor, $pause_check);
        }

        // not supported
        $cursor->markCompleted(); // mark it complete so that clients know to move on and not pause
        return null;
    }

    public function supportsUsersourceAdapter($adapter_class)
    {
        foreach ($this->syncers as $syncer) {
            if ($syncer->supportsUsersourceAdapter($adapter_class)) {
                return true;
            }
        }

        return false;
    }

    public function supportsUsersource(Usersource $usersource)
    {
        if (!$this->isSyncable($usersource)) {
            return false;
        }

        foreach ($this->syncers as $syncer) {
            if ($syncer->supportsUsersource($usersource)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Usersource $usersource
     * @return UsersourceSyncLog|null
     */
    public function getMostRecentLog(Usersource $usersource)
    {
        return $this->repo->getLastStartedLogForUsersource($usersource);
    }

    /**
     * @param Usersource $usersource
     * @return UsersourceSyncLog|null
     */
    public function getLogToUseDuringSync(Usersource $usersource, $force_create = false)
    {
        if ($force_create) {
            return $this->repo->createNewLog($usersource);
        }

        return $this->repo->getOrCreateLogInProgressForUsersource($usersource);
    }

    public function saveLog(UsersourceSyncLog $log)
    {
        $this->repo->save($log);
    }

    /**
     * @param Usersource $usersource
     * @return bool
     */
    public function isSyncable(Usersource $usersource)
    {
        return $usersource->isEnabled() && $usersource->isSyncEnabled();
    }
}
