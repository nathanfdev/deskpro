<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

use Application\DeskPRO\Entity\Person;

/**
 * Keep current datetime to make relative date intervals consistent in dpql placeholders.
 */
class DpqlContext
{
    /**
     * @var Person
     */
    private $person;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * Constructor.
     *
     * @param Person|null $person
     */
    public function __construct(Person $person = null)
    {
        $this->person = $person;
        $this->date   = new \DateTime('now', $this->getTimezone());
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return clone $this->date;
    }

    /**
     * @return Person|null
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimezone()
    {
        $person = $this->person;
        if ($person) {
            return new \DateTimeZone($person->getTimezone());
        }

        return new \DateTimeZone('UTC');
    }
}
