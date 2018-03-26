<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;

class NewChat extends ActionTypeAbstract
{
    /** @var \Application\DeskPRO\Entity\ChatConversation */
    protected $convo;

    /**
     * @param \Application\DeskPRO\Entity\Person           $person
     * @param \Application\DeskPRO\Entity\ChatConversation $convo
     */
    public function __construct(Person $person, ChatConversation $convo)
    {
        $this->person = $person;
        $this->convo  = $convo;
    }

    /**
     * Get a plain array of details that'll be stored in the databaes.
     *
     * @return array
     */
    public function getDetails()
    {
        return [
            'convo_id' => $this->convo['id'],
        ];
    }
}
