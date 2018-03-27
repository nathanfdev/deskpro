<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class IncomingEmailFailureEvent.
 *
 * @ORM\Entity
 */
class JiraApiExceptionEvent extends ExceptionEvent
{
    /**
     * {@inheritdoc}
     */
    public function getSubjectDescription()
    {
        return "Jira exception: \"{$this->message}\" of type {$this->class}";
    }
}
