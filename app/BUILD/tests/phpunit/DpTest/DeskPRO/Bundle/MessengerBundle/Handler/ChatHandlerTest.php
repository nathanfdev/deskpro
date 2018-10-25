<?php

namespace DpTest\DeskPRO\Bundle\MessengerBundle\Handler;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\MessengerBundle\Exception\MapperException;
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

    public function testHandleNewMessageFault()
    {
        $handler = $this->getContainer()->get('messenger.handlers.chat');

        $request = [
            'type'    => 'chat.message',
            'author'  => 999999,
            'message' => '',
        ];

        $chat = new ChatConversation();

        try {
            $handler->handle($chat, $request);
        } catch (\Exception $e) {
            $this->assertInstanceOf(MapperException::class, $e);
            /** @var MapperException $e */
            $expectedErrors = [
                'author'  => 'Wrong author id (999999) given. Couldn\'t find author',
                'message' => 'Message could not be empty',
                'origin'  => 'Unexpected value. Only user and agent are allowed.',
            ];
            $this->assertEquals($expectedErrors, $e->getErrors());
        }
    }

    public function testHandleNewMessage()
    {
        $handler = $this->getContainer()->get('messenger.handlers.chat');

        $request = [
            'type'    => 'chat.message',
            'message' => '<scrpit>alert(\'test\')</scrpit>Test message',
            'origin'  => 'user',
        ];

        $chat       = new ChatConversation();
        $actualData = $handler->handle($chat, $request);
        $message    = $chat->getMessages()->current();

        $expectedData = [
            'name'         => '',
            'date_created' => $message->getDateCreated()->format(\DateTime::ISO8601),
            'avatar'       => '',
            'message'      => 'alert(\'test\')Test message',
            'is_user'      => true,
            'is_sys'       => false,
            'is_html'      => true,
            'origin'       => ChatMessage::ORIGIN_USER,
            'author'       => 0,
            'chat'         => $chat->getId(),
            'id'           => $message->getId(),
        ];
        $this->assertEquals($expectedData, $actualData);
    }
}
