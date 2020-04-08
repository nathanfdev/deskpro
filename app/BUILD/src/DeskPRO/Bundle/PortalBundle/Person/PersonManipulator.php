<?php

namespace DeskPRO\Bundle\PortalBundle\Person;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\DpFormLoginToken;
use DeskPRO\Bundle\AppBundle\Security\LimitEmailDomainsChecker;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Some utilities that we use throughout the portal for people.
 *
 * - Log someone in programatically
 * - Send someone to a page to set their password
 * - etc
 */
class PersonManipulator
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var LimitEmailDomainsChecker
     */
    private $domainsChecker;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface    $urlGenerator
     * @param TokenStorage             $tokenStorage
     * @param LimitEmailDomainsChecker $domainsChecker
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, TokenStorage $tokenStorage, LimitEmailDomainsChecker $domainsChecker)
    {
        $this->urlGenerator   = $urlGenerator;
        $this->tokenStorage   = $tokenStorage;
        $this->domainsChecker = $domainsChecker;
    }

    /**
     * Programatically log a person in. Subsequent listeners provided by symfony will set the proper cookies, session, etc.
     *
     * @param Person $person
     * @param bool   $forceIsUser
     *
     * @return bool true on success, false on failure
     */
    public function authenticatePerson(Person $person, $forceIsUser = true)
    {
        if ($person->isUser() || !$forceIsUser) {
            if (!$this->domainsChecker->checkPerson($person)) {
                return false;
            }

            $token = new DpFormLoginToken($person, null, $person->getRoles());
            $this->tokenStorage->setToken($token);

            return true;
        }

        return false;
    }

    /**
     * @param Person $person
     * @param null   $emailAddress
     */
    public function validatePerson(Person $person, $emailAddress = null)
    {
        // mark the person confirmed, but do not do ->is_user = true because we don't know yet that they can login
        $person->is_confirmed = true;

        // validate the email that the person used when clicking this validation link
        if ($emailAddress) {
            if (!$person_email = $person->getEmailByAddress($emailAddress)) {
                throw new \InvalidArgumentException(
                    sprintf('person does not have email address "%s" - cannot validate it', $emailAddress)
                );
            }
            $person_email->setIsValidated(true);
        }

        // if the user has a password, we know they are a user
        // this should already be set, but let's be sure
        if ($person->getPassword()) {
            $person->is_user = true;
        }
    }
}
