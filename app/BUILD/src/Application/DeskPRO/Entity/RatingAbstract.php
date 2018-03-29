<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;

/**
 * Basic ratings.
 */
abstract class RatingAbstract extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\SearchLog
     */
    protected $searchlog = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * @var string
     */
    protected $visitor_id = null;

    /**
     * @var string
     */
    protected $ip_address = '';

    /**
     * @var string
     */
    protected $email = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $rating;

    /**
     * @var \DateTime
     */
    protected $date_created;

    public static function create($user_rating, $use_request = true)
    {
        $rating         = new static();
        $rating->rating = $user_rating;

        if ($use_request && App::has('request')) {
            if (!App::getCurrentPerson()->isGuest()) {
                $rating->person = App::getCurrentPerson();
            }
        }

        return $rating;
    }

    public function __construct()
    {
        $this['date_created'] = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setRating($rating)
    {
        if ($rating > 0) {
            $this->setModelField('rating', 1);
        } else {
            $this->setModelField('rating', -1);
        }
    }

    public function isPositive()
    {
        return $this->rating > 0;
    }

    public function isNegative()
    {
        return !$this->isPositive();
    }

    public function rateUp()
    {
        $this->setRating(1);
    }

    public function rateDown()
    {
        $this->setRating(-1);
    }

    public function getPersonId()
    {
        if ($this->person) {
            return $this->person->getId();
        }

        return 0;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->visitor_id;
    }

    /**
     * @param string $visitor_id
     */
    public function setVisitorId($visitor_id)
    {
        $this->setModelField('visitor_id', $visitor_id);
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param string $ip_address
     */
    public function setIpAddress($ip_address)
    {
        $this->setModelField('ip_address', $ip_address);
    }

    abstract public function setContentObject($obj);

    /**
     * @return string
     */
    public function getRating()
    {
        return $this->rating;
    }
}
