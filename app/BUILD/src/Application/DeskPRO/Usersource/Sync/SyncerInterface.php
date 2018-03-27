<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Sync;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;

interface SyncerInterface
{
    /**
     * Take a person and retrieve remote data for that person, and update the person's data accordingly.
     *
     * @param Usersource $usersource
     * @param string     $identity_or_email
     *
     * @return bool
     */
    public function refreshIdentity(Usersource $usersource, $identity_or_email);

    /**
     * This method will go out into the remote usersource and download all user information (whether a deskpro user or
     * not yet) and proceed to update people in our database with this information. If a user in our db cannot be found
     * for a record, it will create one.
     *
     * Since this may take a long time, a $pause_check callable is used. After every iterable operation is complete, you
     * must call $pause_check($cursor) with the cursor you were originally given. If this call returns TRUE, you may
     * continue iterating, but if it is FALSE you must stop iteration and return immediately.
     *
     * The $cursor is to be used interally by you to keep track of where you leave off in the case of a TRUE $pause_check
     * and it must contain the information required to resume from where you are pausing.
     *
     * @param SyncCursor $cursor
     * @param callable   $pause_check a callable that takes the SyncCursor originally passed as an argument
     *
     * @return mixed
     */
    public function refreshAll(Usersource $usersource, SyncCursor $cursor, $pause_check);

    /**
     * True if we should use this syncer for the usersource adapter class.
     *
     * @param string $adapter_class the usersource adapter's FQCN
     *
     * @return bool
     */
    public function supportsUsersourceAdapter($adapter_class);

    /**
     * True if we should use this syncer for the usersource adapter class.
     *
     * @param Usersource $usersource
     *
     * @return bool
     */
    public function supportsUsersource(Usersource $usersource);
}
