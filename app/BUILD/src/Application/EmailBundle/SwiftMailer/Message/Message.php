<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SwiftMailer\Message;

use Application\DeskPRO\Mail\Message as DeskproMessage;
use Orb\Util\OptionsArray;

class Message extends DeskproMessage implements MessageOptionsInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @return \Orb\Util\OptionsArray
     */
    public function getMessageOptions()
    {
        if ($this->options === null) {
            $this->options = new OptionsArray();
        }

        return $this->options;
    }
}
