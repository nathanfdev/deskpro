<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ExceptionEvent.
 *
 * @ORM\Entity
 */
class ExceptionEvent extends AbstractExceptionEvent
{
    /**
     * {@inheritdoc}
     */
    public function getSubjectDescription()
    {
        return "Exception \"{$this->message}\" of type {$this->class}";
    }

    /**
     * {@inheritdoc}
     */
    protected function generateSubjectUniqueId()
    {
        return $this->class.'-'.$this->code;
    }
}
