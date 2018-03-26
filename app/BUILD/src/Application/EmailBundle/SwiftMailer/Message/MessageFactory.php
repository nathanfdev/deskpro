<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SwiftMailer\Message;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class MessageFactory implements MessageFactoryInterface
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param string $type
     *
     * @return \Swift_Message
     */
    public function createMessage($type)
    {
        if ($type == 'message') {
            $message = Message::newInstance();
            $message->setEncoder(\Swift_Encoding::get8BitEncoding());
            $message->setTemplateEngine($this->templating);

            return $message;
        }

        return;
    }
}
