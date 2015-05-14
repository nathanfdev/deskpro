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

    public function handleEmail(Person $person, $email_string)
    {
        if ($person->hasEmailAddress($email_string)) {
            return; // already exists
        }

        if ($email = $this->em->getRepository('DeskPRO:PersonEmail')->getEmail($email_string)) {
            if ($email->person->id != $person->id) {
                // uh-oh, the person we are dealing with is not the person who
                // owns this email address...
                // TODO: what to do here?
            }
            return;
        }

        $person->addEmailAddressString($email_string);
    }

    /**
     * @param Usersource $usersource
     * @param Person $person
     * @return PersonUsersourceAssoc
     */
    public function getAssociation(Usersource $usersource, Person $person)
    {
        return $this->em->getRepository('DeskPRO:PersonUsersourceAssoc')
            ->getAssociationForPersonUsersourcePair($person, $usersource);
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
     * returns a new person object, ready to be populated by the usersource syncer
     */
    public function createPerson(array $user_info)
    {
        $person = Person::newContactPerson($user_info);
    }
}
