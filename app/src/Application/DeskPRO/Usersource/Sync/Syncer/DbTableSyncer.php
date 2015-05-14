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

namespace Application\DeskPRO\Usersource\Sync\Syncer;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Sync\SyncCursor;
use Application\DeskPRO\Usersource\Sync\SyncException;
use Orb\Auth\Identity;

class DbTableSyncer extends AbstractSyncer
{
    public function refreshPerson(Usersource $usersource, Person $person)
    {
        $association = $this->helper->getAssociation($usersource, $person);
        $db_adapter = $this->getAdapter($usersource);
        $identity = $db_adapter->findIdentityByInput($association->identity);

        // if the assoc identity didnt work, its possible to find them by their email
        if (!$identity) {
            foreach ($person->getEmailAddresses() as $email) {
                if ($identity = $db_adapter->findIdentityByInput($email)) {
                    break;
                }
            }
        }

        if (!$identity instanceof Identity) {
            throw new SyncException('identity could not be found', $person, $usersource);
        }

        $user_info = $db_adapter->getFieldsFromIdentity($identity);

        // the name part below can also be a helper
        if (!$name = $user_info['name']) {
            $name = $user_info['first_name'] . ' ' . $user_info['last_name'];
        }

        $person->setName($name);
        $this->helper->handleEmail($person, $user_info['email']);

        $this->helper->savePerson($person);
    }

    public function downloadAndRefreshAll(Usersource $usersource, SyncCursor $cursor, callable $pause_check)
    {
        $rows = array(); // add some methods on the adapter to allow for this sort of request

        foreach ($rows as $row) {

            // process row

            $cursor->incrementLocation();
            if ($pause_check($cursor)) {
                return;
            }
        }
    }

    public function supportsUsersourceAdapter($adapter_class)
    {
        return 'Application\DeskPRO\Usersource\Adapter\DbTablePhpPasswordCheck' === $adapter_class;
    }

    /**
     * @param Usersource $usersource
     * @return \Application\DeskPRO\Usersource\Adapter\DbTablePhpPasswordCheck
     */
    protected function getAdapter(Usersource $usersource)
    {
        return $usersource->getAdapter();
    }
}
