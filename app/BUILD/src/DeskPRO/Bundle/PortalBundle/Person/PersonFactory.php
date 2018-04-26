<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Person;

use Application\DeskPRO\Auth\AuthenticationManager;
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

/**
 * Class PersonFactory.
 */
class PersonFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    private $brandStack;

    /**
     * @var LanguageStack
     */
    private $languageStack;

    /**
     * @var UserRuleProcessor
     */
    private $userRuleProcessor;

    /**
     * @var AuthenticationManager
     */
    private $authManager;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param EventDispatcherInterface $eventDispatcher
     * @param BrandStack               $brandStack
     * @param LanguageStack            $languageStack
     * @param UserRuleProcessor        $userRuleProcessor
     * @param AuthenticationManager    $authManager
     */
    public function __construct(
        EntityManager            $em,
        EventDispatcherInterface $eventDispatcher,
        BrandStack               $brandStack,
        LanguageStack            $languageStack,
        UserRuleProcessor        $userRuleProcessor,
        AuthenticationManager    $authManager
    ) {
        $this->em                = $em;
        $this->brandStack        = $brandStack;
        $this->eventDispatcher   = $eventDispatcher;
        $this->languageStack     = $languageStack;
        $this->userRuleProcessor = $userRuleProcessor;
        $this->authManager       = $authManager;
    }

    /**
     * @return Person
     */
    public function createNewPerson()
    {
        $person = new Person();
        $person->setLanguage($this->languageStack->getActiveOrDefault());
        $person->addBrand($this->brandStack->getActive()->getBrand());

        return $person;
    }

    /**
     * @param string              $raw_email
     * @param CreatePersonContext $context
     *
     * @return Person
     */
    public function createPersonByEmail($raw_email, CreatePersonContext $context)
    {
        $person = new Person();

        $name = $context->getName();
        if ($name) {
            $person->setName($name);
        }

        $person->setLanguage($this->languageStack->getActiveOrDefault());
        $person->addBrand($this->brandStack->getActive()->getBrand());

        $email = new PersonEmail();
        $email->setEmail($raw_email);
        $email->person = $person;

        $person->addEmailAddress($email);

        $this->eventDispatcher->dispatch(Person::EVENT_PRE_CREATE, new PersonCreateEvent($person, $context));

        $this->em->persist($person);
        $this->em->flush();

        $this->eventDispatcher->dispatch(Person::EVENT_POST_CREATE, new PersonCreateEvent($person, $context));

        return $person;
    }

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    protected function getBrandSetting($name, $default = null)
    {
        return $this->brandStack->getActive()->getSetting($name, $default);
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
        $this->userRuleProcessor->newRegister($person);

        if (!$person->getLanguage()) {
            $person->setLanguage($this->languageStack->getActiveOrDefault());
        }
        $person->addBrand($this->brandStack->getActive()->getBrand());

        $this->eventDispatcher->dispatch(Person::EVENT_PRE_CREATE, new PersonCreateEvent($person, $context));

        $this->em->persist($person);
        $this->em->flush();

        $this->eventDispatcher->dispatch(Person::EVENT_POST_CREATE, new PersonCreateEvent($person, $context));

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
     *
     * @param PersonGuest $guest
     * @param bool        $already_validated
     *
     * @throws \Exception
     *
     * @return bool
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
            $email = $this->em->getRepository('DeskPRO:PersonEmail')->getEmail($guest_email);
            if ($email) {
                $person = $email->getPerson();
            }
        }

        // if user is not logged in and reg is disabled then we should redirect the user to the login form
        if (!$this->authManager->isRegistrationFormVisible()) {
            throw new LoginRequiredException($guest);
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

    /**
     * @param string $email_address
     * @param string $name
     *
     * @return Person
     */
    public function createPersonFromGuestInfo($email_address, $name)
    {
        $person = Person::newContactPerson([
            'email' => $email_address,
            'name'  => $name,
        ]);
        $person->setLanguage($this->languageStack->getActiveOrDefault());
        $person->addBrand($this->brandStack->getActive()->getBrand());

        // saveNewPerson() will check settings and take care of validation flags
        $this->saveNewPerson($person, new CreatePersonContext('gateway.person'));

        return $person;
    }

    /**
     * @param string              $email
     * @param CreatePersonContext $context
     *
     * @return Person
     */
    public function getOrCreatePersonByEmail($email, CreatePersonContext $context)
    {
        $person = $this->getPersonByEmail($email);
        if ($person) {
            return $person;
        }

        return $this->createPersonByEmail($email, $context);
    }

    /**
     * @param string $email
     *
     * @return Person
     */
    public function getPersonByEmail($email)
    {
        if ($email instanceof PersonEmail) {
            $email = $email->email;
        }

        return $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
    }
}
