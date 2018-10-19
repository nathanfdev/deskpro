<?php

namespace DpTest\DeskPRO\Bundle\MessengerBundle\Handler;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\MessengerBundle\Exception\MessengerApiException;
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
            $this->assertInstanceOf(MessengerApiException::class, $e);
            /** @var MessengerApiException $e */
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
        $uuid       = $message->getMetadata()['uuid'];

        $this->assertEquals(ChatMessage::ORIGIN_USER, $actualData['origin']);
        $this->assertEquals($chat->getId(), $actualData['chat']);
        $this->assertEquals($message->getId(), $actualData['id']);
        $this->assertEquals($uuid, $actualData['uuid']);
        $this->assertEquals('alert(\'test\')Test message', $actualData['message']);
        $this->assertTrue($actualData['is_user']);
        $this->assertTrue($actualData['is_html']);
        $this->assertFalse($actualData['is_sys']);
        $this->assertContains('/file.php/avatar/80/default.jpg?size-fit=1', $actualData['avatar']);
    }
}
