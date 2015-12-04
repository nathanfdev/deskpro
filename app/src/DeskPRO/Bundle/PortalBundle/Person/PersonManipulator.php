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

namespace DeskPRO\Bundle\PortalBundle\Person;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\DpFormLoginToken;
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
    private $url_generator;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    public function __construct(UrlGeneratorInterface $url_generator, TokenStorage $token_storage)
    {
        $this->url_generator = $url_generator;
        $this->token_storage = $token_storage;
    }

    /**
     * Programatically log a person in. Subsequent listeners provided by symfony will set the proper cookies, session, etc.
     *
     * @param Person $person
     *
     * @return bool true on success, false on failure
     */
    public function authenticatePerson(Person $person, $force_is_user = true)
    {
        if ($person->isUser() || !$force_is_user) {
            $token = new DpFormLoginToken($person, null, $person->getRoles());
            $this->token_storage->setToken($token);

            return true;
        }

        return false;
    }

    public function validatePerson(Person $person, $email_address = null)
    {
        // mark the person confirmed, but do not do ->is_user = true because we don't know yet that they can login
        $person->is_confirmed = true;

        // validate the email that the person used when clicking this validation link
        if ($email_address) {
            if (!$person_email = $person->getEmailByAddress($email_address)) {
                throw new \InvalidArgumentException(
                    sprintf('person does not have email address "%s" - cannot validate it', $email_address)
                );
            }
            $person_email->is_validated = true;
        }

        // if the user has a password, we know they are a user
        // this should already be set, but let's be sure
        if ($person->getPassword()) {
            $person->is_user = true;
        }
    }
}
