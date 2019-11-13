<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use Application\DeskPRO\Entity\Department;
use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerTickets.
 */
class MessengerTickets
{
    /**
     * Are tickets enabled.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $enabled = false;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $subject = 'Ticket from {name}';

    /**
     * @JMS\Type("integer")
     *
     * @var string
     */
    private $department;

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

    /**
     * @return string
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
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }
}
