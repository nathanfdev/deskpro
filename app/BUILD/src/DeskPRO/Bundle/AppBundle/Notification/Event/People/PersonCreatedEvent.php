<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractLegacyEvent;

/**
 * Class PersonCreatedEvent.
 */
class PersonCreatedEvent extends AbstractLegacyEvent
{
    const EVENT_NAME = 'person.created';

    /**
     * @var int
     */
    protected $personId;

    /**
     * @var string
     */
    protected $personName;

    /**
     * @var int
     */
    protected $personDateCreated;

    /**
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        parent::__construct('person.created');
        $this->personId          = $person->getId();
        $this->personName        = $person->getName();
        $this->personDateCreated = $person->getDateCreated()->getTimestamp();
    }

    /**
     * @return int
     */
    public function getPersonId()
    {
        return $this->personId;
    }

    /**
     * @return string
     */
    public function getPersonName()
    {
        return $this->personName;
    }

    /**
     * @return int
     */
    public function getPersonDateCreated()
    {
        return $this->personDateCreated;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array_merge(
            parent::__sleep(),
            [
                'personId',
                'personName',
                'personDateCreated',
            ]
        );
    }
}
