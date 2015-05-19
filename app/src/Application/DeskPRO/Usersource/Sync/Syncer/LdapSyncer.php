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
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Sync\SyncCursor;
use Application\DeskPRO\Usersource\Sync\SyncException;
use Doctrine\DBAL\Connection;
use Orb\Auth\Identity;
use Orb\Util\Arrays;
use Orb\Validator\StringEmail;
use Symfony\Component\Validator\Constraints\EmailValidator;
use Zend\Ldap\Exception\LdapException;

class LdapSyncer extends AbstractSyncer
{
    const TMP_DATA_NAME = 'LdapSyncer_synced_raw_info';

    public function refreshAll(Usersource $usersource, SyncCursor $cursor, callable $pause_check)
    {
        if ($cursor->getPhase() == 1) {
            $this->runFirstPass($usersource, $cursor, $pause_check);
        }

        if ($cursor->getPhase() == 2){
            $this->runSecondPass($usersource, $cursor, $pause_check);
        }

        $this->helper->getEm()->flush();

        if ($cursor->getPhase() == 3) {
            $cursor->markCompleted();
        }
    }

    public function runSecondPass(Usersource $usersource, SyncCursor $cursor, callable $pause_check)
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->helper->getEm()->getConnection();
        $tmp_ids_to_remove = array();
        $rows = $conn->fetchAll('SELECT * FROM tmp_data WHERE name = :name', array('name' => self::TMP_DATA_NAME));
        foreach ($rows as $row) {
            $data = unserialize($row['data']);
            if (isset($data['raw_info'])) {
                $raw_info = $data['raw_info'];
                $identity = new Identity($raw_info['identity'], $raw_info);
                $this->syncIdentityWithUsersource($usersource, $identity, $identity->getIdentity());
            }
            $tmp_ids_to_remove[] = $row['id'];

            $cursor->incrementLocation();
            if ($pause_check($cursor)) {
                // get rid of the tmp dat we dealt with in this round
                $conn->executeQuery(
                    'DELETE FROM tmp_data WHERE id IN (:ids)',
                    array('ids' => $tmp_ids_to_remove),
                    array('ids' => Connection::PARAM_INT_ARRAY)
                );
                return;
            }
        }

        $conn->executeQuery(
            'DELETE FROM tmp_data WHERE id IN (:ids)',
            array('ids' => $tmp_ids_to_remove),
            array('ids' => Connection::PARAM_INT_ARRAY)
        );

        // move us on from here, finished the ldap sync
        $cursor->setPhase(3);
    }

    public function runFirstPass(Usersource $usersource, SyncCursor $cursor, callable $pause_check)
    {
        /** @var \Application\DeskPRO\Usersource\Adapter\Ldap $adapter */
        $adapter = $this->getAdapter($usersource);
        $records = $adapter->findAllRecords();

        // records is an iterator, that handles our memory for us. using foreach is worse because we
        // do NOT want to call $records->current() unless we need to, but foreach always calls it
        $start_location = $cursor->getLocation();
        try {
            $records->rewind();
        } catch (LdapException $e) {
            // originally this was in the "for" declaration below, but when the cursor is empty it throws an exception on rewind
        }
        for ($i = 1; $records->valid(); $i++) {
            try {
                $records->next();
            } catch (LdapException $e) {
                // expected behaviour on the last iteration. strang, because $records->valid() passes.
                break;
            }
            if ($i < $start_location) {
                continue; // save us from hitting the LDAP server if we've already visited this record before
            }

            // save record for processing in phase 2
            $raw_info = $records->current();
            $processed_raw_info = $this->processRawInfo($raw_info);
            $tmp = new TmpData();
            $tmp->setData('raw_info', $processed_raw_info);
            $tmp->name = self::TMP_DATA_NAME;
            $this->helper->getEm()->persist($tmp);

            $cursor->incrementLocation();

            if ($pause_check($cursor)) {
                $this->helper->getEm()->flush();
                return;
            }
        }

        $cursor->setPhase(2);
        $cursor->setLocation(1);
    }

    public function refreshIdentity(Usersource $usersource, $identity_or_email)
    {
        $ldap_adapter = $this->getAdapter($usersource);
        $identity = $ldap_adapter->findIdentityByInput($identity_or_email);

        // if the id doesn't exist in the ldap, we make a last-ditch effort to
        // find the usersource assocation via email
        if (!$identity instanceof Identity && StringEmail::isValueValid($identity_or_email)) {
            $person = $this->helper->getPersonFromEmail($identity_or_email);
            if ($assoc = $this->helper->getAssociation($usersource, $person)) {
                $identity = $ldap_adapter->findIdentityByInput($assoc->identity);
            }
        }

        if (!$identity instanceof Identity) {
            throw new SyncException(
                sprintf(
                    'could not find remote identity for identity=%s at usersource id=%s',
                    $identity_or_email,
                    $usersource->getId()
                ),
                $identity_or_email,
                $usersource
            );
        }

        $this->syncIdentityWithUsersource($usersource, $identity, $identity_or_email);

        return true;
    }

    public function supportsUsersourceAdapter($adapter_class)
    {
        return in_array($adapter_class, array(
            'Application\DeskPRO\Usersource\Adapter\Ldap',
            'Application\DeskPRO\Usersource\Adapter\ActiveDirectory'
        ));
    }

    /**
     * @param Usersource $usersource
     * @return \Application\DeskPRO\Usersource\Adapter\Ldap
     */
    protected function getAdapter(Usersource $usersource)
    {
        return $usersource->getAdapter();
    }

    /**
     * @param Usersource $usersource
     * @param Identity $identity
     * @param string $email pass $identity->getIdentity() if no email available to try
     */
    protected function syncIdentityWithUsersource(Usersource $usersource, Identity $identity, $email)
    {
        // get an array of info passed to us from remote usersource
        $user_info = $this->getAdapter($usersource)->getFieldsFromIdentity($identity);

        if ($assoc = $this->helper->getAssociation($usersource, $identity->getIdentity())) {
            $person = $assoc->person;
        } else {
            $person = $this->helper->getPersonFromEmail($email);
            // its ok that this might be null, because our helper deals with null person
        }

        // sync person and assoc
        $person = $this->helper->updateOrCreatePersonWithInfo($user_info, $person);
        $assoc = $this->helper->updateOrCreateAssociation($usersource, $person, $identity);

        $this->helper->savePerson($person);
        $this->helper->saveAssociation($assoc);

        // detach
        $this->helper->getEm()->detach($person);
        $this->helper->getEm()->detach($assoc);
    }

    /**
     * @param $raw_info
     * @return Identity
     */
    protected function processRawInfo($raw_info)
    {
        // normalize the returned data
        if (!empty($raw_info['samaccountname'])) {
            $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['samaccountname']);
        } elseif (!empty($raw_info['uid'])) {
            $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['uid']);
        }
        if (!empty($raw_info['distinguishedname'])) {
            $raw_info['identity'] = Arrays::getFirstItem($raw_info['distinguishedname']);
        } else {
            if (is_array($raw_info['dn'])) {
                $raw_info['identity'] = Arrays::getFirstItem($raw_info['dn']);
            } else {
                $raw_info['identity'] = (string)$raw_info['dn'];
            }
        }
        if ($raw_info['givenname']) {
            $raw_info['first_name'] = Arrays::getFirstItem($raw_info['givenname']);
        }
        if ($raw_info['sn']) {
            $raw_info['last_name'] = Arrays::getFirstItem($raw_info['sn']);
        }
        if (isset($raw_info['first_name']) && isset($raw_info['last_name'])) {
            $raw_info['name'] = $raw_info['first_name'] . ' ' . $raw_info['last_name'];
        } elseif ($raw_info['name']) {
            $raw_info['name'] = Arrays::getFirstItem($raw_info['name']);
        } elseif ($raw_info['cn']) {
            $raw_info['name'] = Arrays::getFirstItem($raw_info['cn']);
        }
        if (isset($raw_info['mail'])) {
            $raw_info['email_address'] = Arrays::getFirstItem($raw_info['mail']);
        }
        if (isset($raw_info['jpegphoto'])) {
            $raw_info['picture_data'] = Arrays::getFirstItem($raw_info['jpegphoto']);
        } elseif (isset($raw_info['thumbnailphoto'])) {
            $raw_info['thumbnailphoto'] = Arrays::getFirstItem($raw_info['thumbnailphoto']);
        }
        if (isset($raw_info['telephonenumber'])) {
            $raw_info['phone'] = Arrays::getFirstItem($raw_info['telephonenumber']);
        } elseif (isset($raw_info['homephone'])) {
            $raw_info['phone'] = Arrays::getFirstItem($raw_info['homephone']);
        } elseif (isset($raw_info['mobile'])) {
            $raw_info['phone'] = Arrays::getFirstItem($raw_info['mobile']);
        }

        return $raw_info;
    }
}
