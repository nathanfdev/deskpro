<?php

namespace Application\DeskPRO\Log\Event;

use Application\DeskPRO\Entity\Person;

class UserMerged extends Base
{
    protected $subject;

    protected $details;

    public function __construct(Person $subject, Person $mergedPerson)
    {
        $this->subject = $subject;
        $this->details = array(
            'id' => $mergedPerson['id'],
            'email' => $mergedPerson->primary_email['email'],
            'name' => $mergedPerson['name'],
        );
    }

    public function getName()
    {
        return 'user_merged';
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getDetails()
    {
        return $this->details;
    }
}
