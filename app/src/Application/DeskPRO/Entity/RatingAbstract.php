<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;

/**
 * Basic ratings
 *
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
        return $this->setRating(-1);
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

    abstract public function setContentObject($obj);
}
