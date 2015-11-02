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

namespace DeskPRO\Bundle\ApiBundle\Model;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PhoneNumber;

/**
 * User profile settings.
 *
 * Class PersonProfile
 */
class PersonProfile
{
    /**
     * @var Person
     */
    private $person;

    /**
     * Constructor.
     *
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->person->getId();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->person->name;
    }

    /**
     * @return string
     */
    public function getOverrideDisplayName()
    {
        return $this->person->override_display_name;
    }

    /**
     * @return null|string
     */
    public function getPrimaryEmail()
    {
        $email = $this->person->getPrimaryEmail();

        return $email ? $email->getEmail() : null;
    }

    /**
     * @return array
     */
    public function getEmails()
    {
        return $this->person->getEmailAddresses();
    }

    /**
     * @return PhoneNumber|null
     */
    public function getPhoneNumber()
    {
        return $this->person->getPrimaryPhoneNumber();
    }

    /**
     * @return int|null
     */
    public function getLanguageId()
    {
        $language = $this->person->getLanguage();

        return $language ? $language->getId() : null;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->person->getTimezone();
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }
}
