<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use Application\DeskPRO\Entity\Department;
use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerChatTicketDefaults.
 */
class MessengerChatTicketDefaults
{
    /**
     * Is chat enabled.
     *
     * @JMS\Type("integer")
     *
     * @var bool
     */
    private $department;

    /**
     * A short prompt to chat.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $subject = 'Missed chat from {name}';

    /**
     * @return int
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setDepartment($department)
    {
        $this->department = $department instanceof Department ? $department->getId() : $department;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }
}
