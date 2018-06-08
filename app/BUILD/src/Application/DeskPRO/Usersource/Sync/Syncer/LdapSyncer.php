<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Sync\Syncer;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Sync\SyncCursor;
use Doctrine\DBAL\Connection;
use Orb\Auth\Identity;
use Orb\Log\Logger;
use Orb\Util\Arrays;
use Orb\Validator\StringEmail;
use Zend\Ldap\Exception\LdapException;

class LdapSyncer extends AbstractSyncer
{
    const TMP_DATA_NAME = 'LdapSyncer_synced_raw_info';

    public function refreshAll(Usersource $usersource, SyncCursor $cursor, $pause_check)
    {
        if (!$usersource->isEnabled()) {
            return;
        }

        if ($cursor->getPhase() == 1) {
            $this->helper->log(Logger::INFO, 'starting phase 1 ['.print_r($cursor, true).']', [$cursor]);
            $this->runFirstPass($usersource, $cursor, $pause_check);
            if ($cursor->getPhase() > 1) {
                $this->helper->log(Logger::INFO, 'pausing phase 1 ['.print_r($cursor, true).']', [$cursor]);
            }
        }

        if ($cursor->getPhase() == 2) {
            $this->helper->log(Logger::INFO, 'starting phase 2 ['.print_r($cursor, true).']', [$cursor]);
            $this->runSecondPass($usersource, $cursor, $pause_check);
            if ($cursor->getPhase() > 1) {
                $this->helper->log(Logger::INFO, 'pausing phase 2 ['.print_r($cursor, true).']', [$cursor]);
            }
        }

        $this->helper->getEm()->flush();

        if ($cursor->getPhase() == 3) {
            $this->helper->log(Logger::INFO, 'finished syncing this usersource, marking it as completed  ['.print_r($cursor, true).']', [$cursor]);
            $cursor->markCompleted();
        }
    }

    public function runSecondPass(Usersource $usersource, SyncCursor $cursor, $pause_check)
    {
        // here we fetch data from tmp_data and actually update/create the person record

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn              = $this->helper->getEm()->getConnection();
        $tmp_ids_to_remove = [];
        $rows              = $conn->fetchAll('SELECT * FROM tmp_data WHERE name = :name', ['name' => self::TMP_DATA_NAME]);
        $total             = count($rows);
        $this->helper->log(Logger::INFO, 'counted '.$total.' left to process in phase 2, starting');
        foreach ($rows as $row) {
            $data = unserialize($row['data']);
            if (isset($data['raw_info'])) {
                $raw_info = $data['raw_info'];
                $identity = new Identity($raw_info['identity'], $raw_info);
                if ($this->syncIdentityWithUsersource($usersource, $identity, $identity->getIdentity())) {
                    $cursor->incrementCounter();
                }
            }
            $tmp_ids_to_remove[] = $row['id'];
            $cursor->incrementLocation();
            if ($pause_check($cursor)) {
                $finished = count($tmp_ids_to_remove);
                $this->helper->log(Logger::INFO, 'time to pause. finished processing '.$finished.' of '.$total.' records.');
                // get rid of the tmp data we dealt with in this round
                $conn->executeQuery(
                    'DELETE FROM tmp_data WHERE id IN (:ids)',
                    ['ids' => $tmp_ids_to_remove],
                    ['ids' => Connection::PARAM_INT_ARRAY]
                );

                return;
            }
        }

        $this->helper->log(Logger::INFO, 'finished processing all remaining ('.$total.') records');
        $conn->executeQuery(
            'DELETE FROM tmp_data WHERE name = :name',
            ['name' => self::TMP_DATA_NAME]
        );

        // move us on from here, finished the ldap sync
        $cursor->setPhase(3);
    }

    public function runFirstPass(Usersource $usersource, SyncCursor $cursor, $pause_check)
    {
        // here we are doing a first pass on the data by fetching it from ldap and putting it into tmp_data

        /** @var \Application\DeskPRO\Usersource\Adapter\Ldap $adapter */
        $adapter = $this->getAdapter($usersource);
        $records = $adapter->findAllRecords();

        // records is an iterator, that handles our memory for us. using foreach is worse because we
        // do NOT want to call $records->current() unless we need to, but foreach always calls it
        $start_location = $cursor->getLocation();

        // ensure we start with a fresh set of tmp_data
        if ($start_location <= 1) {
            $this->helper->log(Logger::INFO, 'deleting all usersource sync temp data from tmp_data table, starting fresh');
            $this->helper->getEm()->getConnection()->executeQuery(
                'DELETE FROM tmp_data WHERE name = :name',
                ['name' => self::TMP_DATA_NAME]
            );
        }

        $this->helper->log(Logger::INFO, 'LDAP: starting a paged search');
        $records->executePagedSearch();
        $this->helper->log(Logger::INFO, 'LDAP: paged search completed');
        $this->helper->log(Logger::INFO, 'LDAP: starting to iterate results');
        $counting_saves   = 0;
        $skip_state_timer = null;
        $skip_counter     = 0;
        $auth_adapter     = $adapter->getAuthAdapter();
        for ($i = 1; $records->valid(); ++$i) {
            try {
                if ($i > 1) {
                    $records->next();
                }
            } catch (LdapException $e) {
            }
            if ($i < $start_location) {
                if (null === $skip_state_timer) {
                    $skip_state_timer = time();
                }
                ++$skip_counter;
                if (0 === $skip_counter % 100) {
                    // log the time it takes for every 100 skipped records
                    $this->helper->log(Logger::INFO, 'at record '.$i.', but skipping to record '.$start_location);
                }
                continue; // save us from hitting the LDAP server if we've already visited this record before
            }
            if (!$raw_info = $records->current()) {
                $this->helper->log(Logger::INFO, 'finished iterating over the LDAP rows, exiting loop');
                break;
            }

            if ($skip_state_timer) {
                $this->helper->log(Logger::INFO, 'spent '.ceil(time() - $skip_state_timer).'s skipping to record '.$start_location);
                $skip_state_timer = null;
            }

            // CHECK FILTER
            if (!$auth_adapter->doesRawInfoPassFilter($raw_info)) {
                $this->helper->log(
                    Logger::INFO,
                    sprintf('user does not meet filter criteria'),
                    [$raw_info]
                )
                ;
                $cursor->incrementLocation();
                if ($pause_check($cursor)) {
                    return;
                }
                continue;
            }

            // save record for processing in phase 2
            $processed_raw_info = $this->processRawInfo($raw_info);

            // NOTE: if we are having problems with not all info being updated, uncomment this line
            // it is MUST slower, but potentially more accurate
            // $processed_raw_info = $adapter->getIdentityForDn($raw_info['identity']);

            $tmp = new TmpData();
            if (isset($processed_raw_info['email_address'])) {
                $tmp->setData('raw_info', $processed_raw_info);
                $tmp->name = self::TMP_DATA_NAME;
                $this->helper->getEm()->persist($tmp);
                $cursor->incrementLocation();
                ++$counting_saves;
            } else {
                // no email found - abort this record
                $cursor->incrementLocation();
                if ($pause_check($cursor)) {
                    $this->helper->getEm()->flush();
                    $this->helper->log(Logger::INFO, 'flushed '.$counting_saves.' tmp records');

                    return;
                }
                continue;
            }

            if ($pause_check($cursor)) {
                $this->helper->getEm()->flush();
                $this->helper->log(Logger::INFO, 'flushed '.$counting_saves.' tmp records');

                return;
            }
        }

        $this->helper->getEm()->flush();
        $this->helper->log(Logger::INFO, 'flushed '.$counting_saves.' tmp records');
        $cursor->setPhase(2);
        $cursor->setLocation(1);
    }

    public function refreshIdentity(Usersource $usersource, $identity_or_email)
    {
        $curTime = microtime(true);
        $this->helper->log(Logger::INFO, 'attempting to refresh the following identity directly ['.$identity_or_email.', usersource='.$usersource->getId().']');
        $ldap_adapter = $this->getAdapter($usersource);
        $identity     = $ldap_adapter->findIdentityByInput($identity_or_email);
        $timeConsumed = round(microtime(true) - $curTime, 3) * 1000;
        if ($timeConsumed >= 5) {
            // only log if it took 1 second or more
            $this->helper->log(Logger::INFO, 'finished looking for identity ['.$identity_or_email.', usersource='.$usersource->getId().'] time (took '.$timeConsumed.'ms) result='.($identity ? 'FOUND' : 'FAILED'));
        }

        // if the id doesn't exist in the ldap, we make a last-ditch effort to
        // find the usersource association via email
        if (!$identity instanceof Identity && StringEmail::isValueValid($identity_or_email)) {
            $person = $this->helper->getPersonFromEmail($identity_or_email);
            if ($assoc = $this->helper->getAssociation($usersource, $person)) {
                $identity = $ldap_adapter->findIdentityByInput($assoc->identity);
            }
        }

        if (!$identity instanceof Identity) {
            return false;
        }

        // FILTER CHECK
        $auth_adapter = $ldap_adapter->getAuthAdapter();
        $raw_info     = $identity->getRawData();
        if (!$auth_adapter->doesRawInfoPassFilter($raw_info)) {
            $this->helper->log(
                Logger::INFO,
                sprintf('user does not meet filter criteria'),
                [$raw_info]
            )
            ;

            return false;
        }

        if (!$this->syncIdentityWithUsersource($usersource, $identity, $identity_or_email)) {
            return false;
        }

        return true;
    }

    public function supportsUsersourceAdapter($adapter_class)
    {
        return in_array($adapter_class, [
            'Application\DeskPRO\Usersource\Adapter\Ldap',
            'Application\DeskPRO\Usersource\Adapter\ActiveDirectory',
        ]);
    }

    /**
     * @param Usersource $usersource
     *
     * @return \Application\DeskPRO\Usersource\Adapter\Ldap
     */
    protected function getAdapter(Usersource $usersource)
    {
        return $usersource->getAdapter();
    }

    /**
     * @param Usersource $usersource
     * @param Identity   $identity
     * @param string     $email      pass $identity->getIdentity() if no email available to try
     */
    protected function syncIdentityWithUsersource(Usersource $usersource, Identity $identity, $email)
    {
        $curTime = microtime(true);
        $this->helper->log(Logger::INFO, 'syncing the following identity ['.$identity->getIdentity().', usersource='.$usersource->getId().']');

        // get an array of info passed to us from remote usersource
        $user_info = $this->getAdapter($usersource)->getFieldsFromIdentity($identity);

        // some adapters REMOVE data in the getFieldFromIdentity call above. We want be sure
        // we use the data it returns, but any extra data from the identity should still be present
        // for user filtering and custom fields.
        $user_info = array_merge($identity->getRawData(), $user_info);

        if ($assoc = $this->helper->getAssociation($usersource, $identity->getIdentity())) {
            $person = $assoc->person;
        } else {
            // prefer raw data over less trustworthy "email" param for a real email
            $rd = $identity->getRawData();
            if (isset($rd['email']) && !empty($rd['email'])) {
                $email = $rd['email'];
            } elseif (isset($rd['email_address']) && !empty($rd['email_address'])) {
                $email = $rd['email_address'];
            }
            $person = $this->helper->getPersonFromEmail($email);
            // its ok that this might be null, because our helper deals with null person
        }

        $person = $this->helper->updateOrCreatePersonWithInfo($user_info, $person, $usersource);
        if (!$person || !$person->getPrimaryEmailAddress() || !$person->getPrimaryEmail()->email) {
            $timeConsumed = round(microtime(true) - $curTime, 3) * 1000;
            $this->helper->log(Logger::INFO, 'unable to sync this identity ['.$identity->getIdentity().', usersource='.$usersource->getId().'] (took '.$timeConsumed.'ms)');

            return false;
        }
        $assoc = $this->helper->updateOrCreateAssociation($usersource, $person, $identity);

        $this->helper->savePerson($person);
        $this->helper->saveAssociation($assoc);

        $timeConsumed = round(microtime(true) - $curTime, 3) * 1000;
        if ($timeConsumed >= 5) {
            // only log if it took 1 second or more
            $this->helper->log(Logger::INFO, 'synced identity ['.$identity->getIdentity().', usersource='.$usersource->getId().'] directly (took '.$timeConsumed.'ms)');
        }

        return true;
    }

    /**
     * @param $raw_info
     *
     * @return Identity
     */
    protected function processRawInfo($raw_info)
    {
        // normalize the returned data
        if (isset($raw_info['samaccountname']) && !empty($raw_info['samaccountname'])) {
            $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['samaccountname']);
        } elseif (isset($raw_info['uid']) && !empty($raw_info['uid'])) {
            $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['uid']);
        }
        if (isset($raw_info['distinguishedname']) && !empty($raw_info['distinguishedname'])) {
            $raw_info['identity'] = Arrays::getFirstItem($raw_info['distinguishedname']);
        } else {
            if (is_array($raw_info['dn'])) {
                $raw_info['identity'] = Arrays::getFirstItem($raw_info['dn']);
            } else {
                $raw_info['identity'] = (string) $raw_info['dn'];
            }
        }
        if (isset($raw_info['givenname']) && $raw_info['givenname']) {
            $raw_info['first_name'] = Arrays::getFirstItem($raw_info['givenname']);
        }
        if (isset($raw_info['sn']) && $raw_info['sn']) {
            $raw_info['last_name'] = Arrays::getFirstItem($raw_info['sn']);
        }
        if (isset($raw_info['first_name']) && isset($raw_info['last_name'])) {
            $raw_info['name'] = $raw_info['first_name'].' '.$raw_info['last_name'];
        } elseif (isset($raw_info['name']) && $raw_info['name']) {
            $raw_info['name'] = Arrays::getFirstItem($raw_info['name']);
        } elseif (isset($raw_info['cn']) && $raw_info['cn']) {
            $raw_info['name'] = Arrays::getFirstItem($raw_info['cn']);
        }
        if (isset($raw_info['mail'])) {
            $raw_info['email_address'] = Arrays::getFirstItem($raw_info['mail']);
        }
        if (isset($raw_info['jpegphoto'])) {
            $raw_info['picture_data'] = Arrays::getFirstItem($raw_info['jpegphoto']);
        } elseif (isset($raw_info['thumbnailphoto'])) {
            $raw_info['picture_data'] = Arrays::getFirstItem($raw_info['thumbnailphoto']);
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
