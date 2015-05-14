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
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use Orb\Auth\Identity;

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

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function updateOrCreatePersonWithInfo(array $user_info, Person $person = null)
    {
        // merge in the default values on $user_info array
        $user_info = array_merge(
            array(
                'name' => null,
                'first_name' => null,
                'last_name' => null,
                'email' => null,
                'email_confirmed' => null
            ),
            $user_info
        );

        if (!$person) {
            $person = Person::newContactPerson(array('email' => $user_info['email']));
            $this->em->persist($person);
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
            if (!$person->hasEmailAddress($user_info['email'])) {
                $person->addEmailAddressString($user_info['email']);
            }
        }

        return $person;
    }

    public function updateOrCreateAssociation(
        Usersource $usersource,
        Person $person,
        Identity $identity
    )
    {
        if (!$assoc = $this->getAssociation($usersource, $person)) {
            $assoc = new PersonUsersourceAssoc();
            $this->em->persist($assoc);
        }

        $assoc->setPerson($person);
        $assoc->setUsersource($usersource);
        $assoc->setIdentity($identity->getIdentity());
        $assoc->setIdentityFriendly($identity->getFriendlyIdentity() ?: $identity->getIdentity());

        return $assoc;
    }

    /**
     * @param Usersource $usersource
     * @param Person|null $person_or_identifier
     * @return PersonUsersourceAssoc
     */
    public function getAssociation(Usersource $usersource, $person_or_identifier = null)
    {
        if (!$person_or_identifier) {
            return null;
        }

        if ($person_or_identifier instanceof Person) {
            if (!$person_or_identifier->getId()) {
                return null; // not yet persisted person, cannot have an assocation yet
            }

            return $this->em->getRepository('DeskPRO:PersonUsersourceAssoc')
                ->getAssociationForPersonUsersourcePair($person_or_identifier, $usersource);
        }

        return $this->getAssoc($usersource, $person_or_identifier);
    }

    /**
     * @param Usersource $usersource
     * @param $identity
     * @return PersonUsersourceAssoc|null
     */
    public function getAssoc(Usersource $usersource, $identity)
    {
        /** @var \Application\DeskPRO\EntityRepository\PersonUsersourceAssoc $assoc_repo */
        $assoc_repo = $this->em->getRepository('DeskPRO:PersonUsersourceAssoc');
        if ($assoc = $assoc_repo->getIdentityAssociation($usersource, $identity)) {
            return $assoc;
        }

        return null;
    }

    /**
     * @param string $email_string
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
    public function savePerson(Person $person)
    {
        $this->em->persist($person);
        $this->em->flush($person);
    }

    /**
     * Saves the association.
     *
     * @param PersonUsersourceAssoc $association
     */
    public function saveAssociation(PersonUsersourceAssoc $association)
    {
        $this->em->persist($association);
        $this->em->flush($association);
    }
}
