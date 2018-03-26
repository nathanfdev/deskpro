<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

/**
 * Tracks changes on objects.
 */
abstract class ChangeLogAbstract extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * Who was responsible for the change.
     *
     * Null without is_system means we don't know (ie user was deleted). Otherwise, we should always
     * have a person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * The users name and email address. This copy is saved incase the user is deleted, we'll at least
     * have the name.
     *
     * @var string
     */
    protected $person_record = null;

    /**
     * @var string
     */
    protected $action_type;

    /**
     * @var string
     */
    protected $details = [];

    /**
     * @var \DateTime
     */
    protected $date_created;

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

    public function getPersonId()
    {
        if ($this->person) {
            return $this->person['id'];
        }

        return 0;
    }
}
