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
namespace DeskPRO\Bundle\PortalBundle\Person;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\People\UserRuleProcessor;
use DeskPRO\Bundle\AppBundle\Language\LanguageStack;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\AppBundle\Person\Events\PersonCreateEvent;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
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
     * @var \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @var LanguageStack
     */
    private $language_stack;

    /**
     * @var UserRuleProcessor
     */
    private $user_rule_processor;

    public function __construct(EntityManager $em, EventDispatcherInterface $event_dispatcher, BrandStack $brand_stack, LanguageStack $language_stack, UserRuleProcessor $user_rule_processor)
    {
        $this->em                  = $em;
        $this->brand_stack         = $brand_stack;
        $this->event_dispatcher    = $event_dispatcher;
        $this->language_stack      = $language_stack;
        $this->user_rule_processor = $user_rule_processor;
    }

    public function createNewPerson()
    {
        $person = new Person();

        $person->setLanguage($this->language_stack->getActiveOrDefault());

        return $person;
    }

    public function createPersonByEmail($raw_email, CreatePersonContext $context)
    {
        $person = new Person();

        if ($name = $context->getName()) {
            $person->setName($name);
        }

        $person->setLanguage($this->language_stack->getActiveOrDefault());

        $email = new PersonEmail();
        $email->setEmail($raw_email);
        $email->person = $person;

        $person->addEmailAddress($email);

        $this->event_dispatcher->dispatch(Person::EVENT_PRE_CREATE, new PersonCreateEvent($person, $context));

        $this->em->persist($person);
        $this->em->flush();

        $this->event_dispatcher->dispatch(Person::EVENT_POST_CREATE, new PersonCreateEvent($person, $context));

        return $person;
    }

    protected function getBrandSetting($name, $default = null)
    {
        return $this->brand_stack->getActive()->getSetting($name, $default);
    }

    /**
     * Saves a new person, this will set validation flags based on settings, so only do this for NEW people.
     *
     * @param Person              $person
     * @param CreatePersonContext $context
     *
     * @return Person
     */
    public function saveNewPerson(Person $person, CreatePersonContext $context)
    {
        $this->user_rule_processor->newRegister($person);

        if (!$person->getLanguage()) {
            $person->setLanguage($this->language_stack->getActiveOrDefault());
        }

        $this->event_dispatcher->dispatch(Person::EVENT_PRE_CREATE, new PersonCreateEvent($person, $context));

        $this->em->persist($person);
        $this->em->flush();

        $this->event_dispatcher->dispatch(Person::EVENT_POST_CREATE, new PersonCreateEvent($person, $context));

        return $person;
    }

    /**
     * Use this method to check to see what actions we need to take for guests in the portal.
     *
     * It will throw one of three exceptions if something need be done:
     *
     * 1. InvalidArgumentException - if the guest does not have an email set - always be sure an email is on the guest.
     * 2. LoginRequiredException - this guest is actually a person who can login, so force a login.
     * 3. EmailValidationRequiredException - this is a new person and we don't want them or their content in the system until
     *                                       they pass email validation.
     */
    public function checkGuestForValidation(PersonGuest $guest, $already_validated = false)
    {
        if ($already_validated) {
            return true;
        }

        /* @var \Application\DeskPRO\Entity\PersonEmail $email */
        if (!$guest_email = $guest->getEmailAddress()) {
            throw new \InvalidArgumentException('guest passed to "checkGuestForValidation" did not have an email. email is required to use this method.');
        }

        if (!$person = $this->getPersonByEmail($guest_email)) {
            if ($email = $this->em->getRepository('DeskPRO:PersonEmail')->getEmail($guest_email)) {
                $person = $email->getPerson();
            }
        }

        if (!$person) {
            // email does not exist, we need to do email validation here
            throw new EmailValidationRequiredException($guest->getEmailAddress(), $guest->name);
        } else {
            if ($person->isUser()) {
                // this person can login, so force a login!
                throw new LoginRequiredException($person);
            } else {
                // this person exists in the db but can't login, send them a validation email!
                throw new EmailValidationRequiredException($guest->getEmailAddress(), $guest->name);
            }
        }
    }

    public function createPersonFromGuestInfo($email_address, $name)
    {
        $person = Person::newContactPerson([
            'email' => $email_address,
            'name'  => $name,
        ]);
        $person->setLanguage($this->language_stack->getActiveOrDefault());

        // saveNewPerson() will check settings and take care of validation flags
        $this->saveNewPerson($person, new CreatePersonContext('gateway.person'));
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
