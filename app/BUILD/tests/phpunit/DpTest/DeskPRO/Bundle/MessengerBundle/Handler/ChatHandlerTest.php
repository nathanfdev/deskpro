<?php

namespace DpTest\DeskPRO\Bundle\MessengerBundle\Handler;

use Application\DeskPRO\Entity\ChatConversation;
use DpTest\MessengerTestCase;

class ChatHandlerTest extends MessengerTestCase
{
    /**
     * @expectedException \Exception
     */
    public function testExceptionWhenHandle()
    {
        $handler = $this->getContainer()->get('messenger.handlers.chat');

        $request = [
            'type' => 'wrong_type',
        ];

        $chat = new ChatConversation();

        $handler->handle($chat, $request);
    }
}
