<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class OAuthExceptionEvent extends ExceptionEvent
{
    /**
     * {@inheritdoc}
     */
    public function getSubjectDescription()
    {
        return "OAuth exception: \"{$this->message}\".";
    }
}
