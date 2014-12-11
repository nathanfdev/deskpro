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
 * @subpackage
 */

namespace Application\PersonBundle\Person;


use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\People\PersonGuest;
use Application\PersonBundle\Events\PersonCreateEvent;
use Application\PersonBundle\Person\Context\CreatePersonContext;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PersonFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $event_dispatcher;

    /**
     * @var \Application\DeskPRO\Brand\BrandStack
     */
    private $brand_stack;

    public function __construct(EntityManager $em, EventDispatcherInterface $event_dispatcher, BrandStack $brand_stack)
    {
        $this->em = $em;
        $this->brand_stack = $brand_stack;
        $this->event_dispatcher = $event_dispatcher;
    }

    public function createNewPerson()
    {
        return new Person();
    }

    public function createPersonByEmail($raw_email, CreatePersonContext $context)
    {
        $person = new Person();

        $email = new PersonEmail();
        $email->setEmail($raw_email);
        $email->person = $person;

        $person->addEmailAddress($email);

        $email->is_validated = true;
        $person->is_confirmed = true;

        $this->event_dispatcher->dispatch(Person::EVENT_PRE_CREATE, new PersonCreateEvent($person, $context));

        $this->em->persist($person);
        $this->em->flush();

        $this->event_dispatcher->dispatch(Person::EVENT_POST_CREATE, new PersonCreateEvent($person, $context));

        return $person;
    }

    public function saveNewPerson(Person $person, CreatePersonContext $context)
    {
        $email = $person->getPrimaryEmail();
        $email->is_validated = true;
        $person->is_confirmed = true;

        $this->event_dispatcher->dispatch(Person::EVENT_PRE_CREATE, new PersonCreateEvent($person, $context));

        $this->em->persist($person);
        $this->em->flush();

        $this->event_dispatcher->dispatch(Person::EVENT_POST_CREATE, new PersonCreateEvent($person, $context));

        return $person;
    }

    public function createPersonFromGuest(PersonGuest $guest)
    {
        $final_person = null;

        $settings = $this->brand_stack->getActive()->getSettings();

        $email = $this->em->getRepository('DeskPRO:PersonEmail')->getEmail($guest->getPrimaryEmail()->email);

        // Email already exists on an account
        // Means use the same person, but depending on the setting we
        // might require the user to log in (in which case the ticket is a temp ticket for a bit)
        if ($email) {
            if ($settings->get('core.existing_account_login')) {
                $person = $email->person;
                $require_login = true; // TODO: redirect to login page.. but do we ignore the ticket? We dont have "temp" ones atm in new portal.
            } else {
                $person = $email->person;
                if ($guest->name) {
                    $person->name = $guest->name;
                    $this->em->persist($person);
                }
            }

            // If we get here, then its a new user. We add the email address
            // as an email address that requires validation. If validation is disabled,
            // we toggles it off
        } else {

            $person = $this->getPersonByEmail($email);

            // Still no, if we're here then we make a new profile
            if (!$person) {
                $person = Person::newContactPerson();
                if ($guest->name) {
                    $person->name = $guest->name;
                }
                $person->getChangeTracker()->recordExtra('email_validating', $guest->primary_email->email);

                if ($settings->get('core.agent_validation')) {
                    $person->is_agent_confirmed = false;
                }

                $email = new PersonEmail();
                $email->setEmail($guest->primary_email->email);
                $email->person = $person;
                $person->addEmailAddress($email);

                $this->em->persist($person);
                $this->em->persist($email);
            }
        }

        $this->em->flush($person);

        return $person;
    }

    public function getOrCreatePersonByEmail($email, CreatePersonContext $context)
    {
        if ($person = $this->getPersonByEmail($email)) {
            return $person;
        }

        return $this->createPersonByEmail($email, $context);
    }

    public function getPersonByEmail($email)
    {
        if ($email instanceof PersonEmail) {
            $email = $email->email;
        }
        return $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
    }
}
 