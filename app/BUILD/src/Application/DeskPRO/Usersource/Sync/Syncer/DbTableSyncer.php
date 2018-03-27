<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Sync\Syncer;

use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Sync\SyncCursor;
use Orb\Auth\Identity;
use Orb\Log\Logger;
use Orb\Validator\StringEmail;

class DbTableSyncer extends AbstractSyncer
{
    public function refreshAll(Usersource $usersource, SyncCursor $cursor, $pause_check)
    {
        if (!$usersource->isEnabled()) {
            return;
        }

        /** @var \Application\DeskPRO\Usersource\Adapter\DbTablePhpPasswordCheck $adapter */
        $adapter = $this->getAdapter($usersource);
        /* @var \Orb\Auth\Identity[] $identities */
        $offset = $cursor->getLocation() - 1; // location starts at 1, but offset starts at 0
        $limit  = 1000;

        /** @var $authAdapter \Orb\Auth\Adapter\DbTable.php */
        $authAdapter = $adapter->getAuthAdapter();
        while ($infos = $authAdapter->getAllUserInfo($offset, $limit)) {
            foreach ($infos as $info) {
                $cursor->incrementLocation();
                if (!$authAdapter->doesRawInfoPassFilter($info)) {
                    $this->helper->log(
                        Logger::INFO,
                        sprintf('user does not meet filter criteria'),
                        [$info]
                    );
                    $cursor->incrementLocation();
                    if ($pause_check($cursor)) {
                        return;
                    }

                    continue;
                }
                $identity = $authAdapter->getIdentityFromUserInfo($info);
                $this->syncIdentityWithUsersource($usersource, $identity, $identity->getIdentity());

                $cursor->incrementCounter();
                if ($pause_check($cursor)) {
                    return;
                }
            }

            $offset += $limit;
        }

        $cursor->markCompleted();
    }

    public function refreshIdentity(Usersource $usersource, $identity_or_email)
    {
        $db_adapter = $this->getAdapter($usersource);
        $identity   = $db_adapter->findIdentityByInput($identity_or_email);

        // if the id doesn't exist in the remote db, we make a last-ditch effort to
        // find the usersource assocation via email
        if (!$identity instanceof Identity && StringEmail::isValueValid($identity_or_email)) {
            $person = $this->helper->getPersonFromEmail($identity_or_email);
            if ($assoc = $this->helper->getAssociation($usersource, $person)) {
                $identity = $db_adapter->findIdentityByInput($assoc->identity);
            }
        }

        if (!$identity instanceof Identity) {
            $this->helper->log(
                Logger::INFO,
                sprintf(
                    'could not find remote identity for identity=%s at usersource id=%s',
                    $identity_or_email,
                    $usersource->getId()
                )
            );

            return false;
        }

        // FILTER CHECK
        $auth_adapter = $db_adapter->getAuthAdapter();
        $raw_info     = $identity->getRawData();
        if (!$auth_adapter->doesRawInfoPassFilter($raw_info)) {
            $this->helper->log(
                Logger::INFO,
                sprintf('user does not meet filter criteria'),
                [$raw_info]
            );

            return false;
        }

        $this->syncIdentityWithUsersource($usersource, $identity, $identity_or_email);

        return true;
    }

    public function supportsUsersourceAdapter($adapter_class)
    {
        return in_array($adapter_class, [
            'Application\DeskPRO\Usersource\Adapter\DbTablePhpPasswordCheck',
            'Application\DeskPRO\Usersource\Adapter\Dp3CustomMysql',
            'Application\DeskPRO\Usersource\Adapter\EzPublish',
        ]);
    }

    /**
     * @param Usersource $usersource
     *
     * @return \Application\DeskPRO\Usersource\Adapter\DbTablePhpPasswordCheck
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
        // get an array of info passed to us from remote usersource
        $user_info = $this->getAdapter($usersource)->getFieldsFromIdentity($identity);

        // some adapters REMOVE data in the getFieldFromIdentity call above. We want be sure
        // we use the data it returns, but any extra data from the identity should still be present
        // for user filtering and custom fields.
        $user_info = array_merge($identity->getRawData(), $user_info);

        if ($assoc = $this->helper->getAssociation($usersource, $identity->getIdentity())) {
            $person = $assoc->person;
        } else {
            $person = $this->helper->getPersonFromEmail($email);
            // its ok that this might be null, because our helper deals with null person
        }

        // sync person and assoc
        $person = $this->helper->updateOrCreatePersonWithInfo($user_info, $person, $usersource);
        if (!$person || !$person->getPrimaryEmailAddress() || !$person->getPrimaryEmail()->email) {
            return false;
        }
        $assoc = $this->helper->updateOrCreateAssociation($usersource, $person, $identity);

        $this->helper->savePerson($person);
        $this->helper->saveAssociation($assoc);

        return true;
    }
}
