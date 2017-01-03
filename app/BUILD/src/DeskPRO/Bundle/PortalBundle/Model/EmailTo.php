<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Model;

use Application\DeskPRO\Entity\Person;

/**
 * Encapsulates logic around who to send an email to. Usually a person (just construct this with a Person) but
 * sometimes we send an email when we don't have a Person object yet, hence the need for this model.
 */
class EmailTo
{
    /**
     * @var Person|null
     */
    protected $person;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $name;

    /**
     * If you provide a Person you won't need to do anything else.
     *
     * If you omit Person from the constructer, you must call ::setTo(email, name)
     *
     * @param Person|null $person
     */
    public function __construct(Person $person = null)
    {
        $this->person = $person;
    }

    /**
     * @param $email_address
     * @param string|bool $name - false means don't change name
     */
    public function setTo($email_address, $name = false)
    {
        $this->email = $email_address;
        if ($name !== false) {
            $this->name = $name;
        }
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getName()
    {
        return $this->name ?: ($this->person ? $this->person->getNameWithTitle() : '');
    }

    public function getEmailAddress()
    {
        return $this->email ?: ($this->person ? $this->person->getPrimaryEmailAddress() : '');
    }
}
