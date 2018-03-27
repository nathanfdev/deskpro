<?php

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
