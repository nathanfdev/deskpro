<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace Application\DeskPRO\Usersource\Sync\Syncer;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Sync\SyncCursor;
use Application\DeskPRO\Usersource\Sync\SyncException;
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
        $offset     = $cursor->getLocation() - 1; // location starts at 1, but offset starts at 0
        $identities = $adapter->findAllIdentities($offset);

        $auth_adapter = $adapter->getAuthAdapter();
        foreach ($identities as $identity) {
            // FILTER CHECK
            $raw_info = $identity->getRawData();
            if (!$auth_adapter->doesRawInfoPassFilter($raw_info)) {
                $this->helper->log(
                    Logger::INFO,
                    sprintf('user does not meet filter criteria'),
                    array($raw_info)
                )
                ;
                $cursor->incrementLocation();
                if ($pause_check($cursor)) {
                    return;
                }

                continue;
            }
            $this->syncIdentityWithUsersource($usersource, $identity, $identity->getIdentity());

            $cursor->incrementLocation();
            $cursor->incrementCounter();
            if ($pause_check($cursor)) {
                return;
            }
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

        // FILTER CHECK
        $auth_adapter = $db_adapter->getAuthAdapter();
        $raw_info     = $identity->getRawData();
        if (!$auth_adapter->doesRawInfoPassFilter($raw_info)) {
            $this->helper->log(
                Logger::INFO,
                sprintf('user does not meet filter criteria'),
                array($raw_info)
            )
            ;

            return false;
        }

        $this->syncIdentityWithUsersource($usersource, $identity, $identity_or_email);

        return true;
    }

    public function supportsUsersourceAdapter($adapter_class)
    {
        return in_array($adapter_class, array(
            'Application\DeskPRO\Usersource\Adapter\DbTablePhpPasswordCheck',
            'Application\DeskPRO\Usersource\Adapter\Dp3CustomMysql',
            'Application\DeskPRO\Usersource\Adapter\EzPublish',
        ));
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
