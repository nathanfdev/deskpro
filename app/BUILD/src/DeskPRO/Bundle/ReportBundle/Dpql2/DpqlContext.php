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
     * @var string
     */
    private $defaultTimezone;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \DateTime
     */
    private $cachedDate;

    /**
     * Constructor.
     *
     * @param Person|null $person
     * @param string      $defaultTimezone
     */
    public function __construct(Person $person = null, $defaultTimezone = null)
    {
        $this->person          = $person;
        $this->defaultTimezone = $defaultTimezone;
    }

    /**
     * @param string $defaultTimezone
     */
    public function setDefaultTimezone($defaultTimezone)
    {
        $this->defaultTimezone = $defaultTimezone;

        // unset date in case it was already defined
        $this->cachedDate = null;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        if (!$this->cachedDate) {
            if ($this->date) {
                $this->cachedDate = clone $this->date;
            } else {
                $this->cachedDate = new \DateTime('now', $this->getTimezone());
            }
        }

        return clone $this->cachedDate;
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

        // unset date in case it was already defined
        $this->cachedDate = null;
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

        if ($this->defaultTimezone instanceof \DateTimeZone) {
            return $this->defaultTimezone;
        } elseif (is_string($this->defaultTimezone)) {
            return new \DateTimeZone($this->defaultTimezone);
        }

        return new \DateTimeZone('UTC');
    }

    /**
     * @return int
     */
    public function getTimezoneOffsetSeconds()
    {
        return $this->getTimezone()->getOffset(new \DateTime('now'));
    }
}
