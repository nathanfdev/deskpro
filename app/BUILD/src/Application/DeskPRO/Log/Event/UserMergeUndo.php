<?php

namespace Application\DeskPRO\Log\Event;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonActivity;

class UserMergeUndo extends Base
{
    protected $subject;

    protected $details;

    public function __construct(Person $subject, PersonActivity $activity)
    {
        $this->subject = $subject;
        $this->details = [
            'id'                 => $subject['id'],
            'email'              => $subject->primary_email['email'],
            'name'               => $subject['name'],
            'merged_activity_id' => $activity->getId(),
        ];
    }

    public function getName()
    {
        return 'user_merge_undo';
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
