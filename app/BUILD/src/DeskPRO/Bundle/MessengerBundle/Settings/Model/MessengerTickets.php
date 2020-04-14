<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use Application\DeskPRO\Entity\Department;
use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerTickets.
 */
class MessengerTickets
{
    const TICKET_DEPARTMENT_OPTION_CHOOSE = 'choose';
    const TICKET_DEPARTMENT_OPTION_HIDDEN = 'hidden';

    const TICKET_SUBJECT_OPTION_PRESET = 'preset';
    const TICKET_SUBJECT_OPTION_USER   = 'user';

    /**
     * Are tickets enabled.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("subjectOption")
     *
     * @var string
     */
    private $subjectOption = self::TICKET_SUBJECT_OPTION_USER;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $subject = 'Ticket from {name}';

    /**
     * @JMS\Type("integer")
     *
     * @var integer
     */
    private $department;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("departmentOption")
     *
     * @var string
     */
    private $departmentOption = self::TICKET_DEPARTMENT_OPTION_CHOOSE;

    /**
     * @var MessengerTicketsOptions
     */
    private $options;

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
    public function getSubjectOption()
    {
        return $this->subjectOption;
    }

    /**
     * @param string $subjectOption
     *
     * @return $this
     */
    public function setSubjectOption($subjectOption)
    {
        $this->subjectOption = $subjectOption;

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

    /**
     * @return string
     */
    public function getDepartmentOption()
    {
        return $this->departmentOption;
    }

    /**
     * @param string $departmentOption
     *
     * @return $this
     */
    public function setDepartmentOption($departmentOption)
    {
        $this->departmentOption = $departmentOption;

        return $this;
    }

    /**
     * @return MessengerTicketsOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param MessengerTicketsOptions $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }
}
