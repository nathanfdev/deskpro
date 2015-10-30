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
namespace Application\DeskPRO\Usersource\Sync;

use Application\DeskPRO\App;
use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\EntityRepository\TmpData as TmpDataRepo;
use Doctrine\ORM\EntityManager;
use Orb\Auth\Identity;
use Orb\Log\Logger;

/**
 * This will be offered as a service to all Syncers. It aids them by taking care of common Syncer needs.
 * A central place so we can reduce code duplication.
 */
class SyncerHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(EntityManager $em, Logger $logger = null)
    {
        $this->em     = $em;
        $this->logger = $logger;
    }

    public function getEm()
    {
        return $this->em;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function log($orb_logger_priority, $message, array $info = array())
    {
        if ($this->logger) {
            $this->logger->log('SYNC: '.$message, $orb_logger_priority, $info);
        }
    }

    public function updateOrCreatePersonWithInfo(array $user_info, Person $person = null, Usersource $usersource)
    {
        // merge in the default values on $user_info array
        if (isset($user_info['email_address'])) {
            $user_info['email'] = $user_info['email_address'];
        }
        $user_info = array_merge(
            array(
                'name'            => null,
                'first_name'      => null,
                'last_name'       => null,
                'email'           => null,
                'email_confirmed' => null,
                'phone'           => null,
            ),
            $user_info
        );

        if (empty($user_info['email'])) {
            return false;
        }

        if (App::$container->getEmailAccountManager()->findAccountForEmailAddress($user_info['email'])) {
            // gateway email, don't process
            return false;
        }

        if (!$person) {
            if (!$person = $this->getPersonFromEmail($user_info['email'])) {
                $person = Person::newContactPerson(array('email' => $user_info['email']));
                $this->em->persist($person);
            }
        }

        if (!empty($user_info['first_name'])) {
            $person->setFirstName($user_info['first_name']);
        }

        if (!empty($user_info['last_name'])) {
            $person->setLastName($user_info['last_name']);
        }

        if (!empty($user_info['name'])) {
            $person->setName($user_info['name']);
        }

        if (!empty($user_info['email'])) {
            // if "$this->getPersonFromEmail($user_info['email'])" is true, we are in a potential merge situation
            // ignoring for now
            if (!$person->hasEmailAddress($user_info['email']) && !$this->getPersonFromEmail($user_info['email'])) {
                $person->addEmailAddressString($user_info['email']);
            }
        }

        if (!empty($user_info['phone'])) {
            if ($number = PhoneNumber::createEntity($user_info['phone'])) {
                $person->setPrimaryPhoneNumber($number);
            }
        }

        // we only attempt an agent promotion during sync if the helpdesk has less than 500 agents
        $count = $this->em->getRepository('DeskPRO:Person')->getActiveAgentsCount();
        if ($count < 500) {
            // tries the auto-agent routine, if agent usersource (just like on login from a usersource)
            LoginProcessor::tryAutoAgent($usersource, $person);
        }
        LoginProcessor::tryUsergroupPromotion($usersource, $person);

        return $person;
    }

    public function updateOrCreateAssociation(
        Usersource $usersource,
        Person $person,
        Identity $identity
    ) {
        if (!$assoc = $this->getAssociation($usersource, $person)) {
            $assoc = new PersonUsersourceAssoc();
            $this->em->persist($assoc);
        }

        $assoc->setPerson($person);
        $assoc->setUsersource($usersource);
        $assoc->setIdentity($identity->getIdentity());
        $assoc->setIdentityFriendly($identity->getFriendlyIdentity() ?: $identity->getIdentity());
        $assoc->setDateUpdated(new \DateTime());

        return $assoc;
    }

    public function persistAndFlushEntity($entity)
    {
        $this->em->persist($entity);
        $this->em->flush($entity);
    }

    /**
     * @param Usersource  $usersource
     * @param Person|null $person_or_identifier
     *
     * @return PersonUsersourceAssoc
     */
    public function getAssociation(Usersource $usersource, $person_or_identifier = null)
    {
        if (!$person_or_identifier) {
            return;
        }

        if ($person_or_identifier instanceof Person) {
            if (!$person_or_identifier->getId()) {
                return; // not yet persisted person, cannot have an assocation yet
            }

            return $this->em->getRepository('DeskPRO:PersonUsersourceAssoc')
                ->getAssociationForPersonUsersourcePair($person_or_identifier, $usersource);
        }

        return $this->getAssoc($usersource, $person_or_identifier);
    }

    /**
     * @param Usersource $usersource
     * @param $identity
     *
     * @return PersonUsersourceAssoc|null
     */
    public function getAssoc(Usersource $usersource, $identity)
    {
        /** @var \Application\DeskPRO\EntityRepository\PersonUsersourceAssoc $assoc_repo */
        $assoc_repo = $this->em->getRepository('DeskPRO:PersonUsersourceAssoc');
        if ($assoc = $assoc_repo->getIdentityAssociation($usersource, $identity)) {
            return $assoc;
        }

        return;
    }

    /**
     * @param string $email_string
     *
     * @return Person|null
     */
    public function getPersonFromEmail($email_string)
    {
        return $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email_string);
    }

    /**
     * Saves a person.
     *
     * @param Person $person
     */
    public function savePerson(Person $person, $flush = true)
    {
        $this->em->persist($person);
        if ($flush) {
            $this->em->flush($person);
        }
    }

    /**
     * Saves the association.
     *
     * @param PersonUsersourceAssoc $association
     */
    public function saveAssociation(PersonUsersourceAssoc $association, $flush = true)
    {
        $this->em->persist($association);
        if ($flush) {
            $this->em->flush($association);
        }
    }

    /**
     * @return TmpDataRepo
     */
    public function getTmpDataRepo()
    {
        return $this->em->getRepository('DeskPRO:TmpData');
    }
}
