<?php

namespace DpTest\DeskPRO\Bundle\MessengerBundle\Mapper;

use Application\DeskPRO\Entity\ChatMessage;
use DpTest\MessengerTestCase;

class ChatMapperTest extends MessengerTestCase
{
    public function testCreateMessage()
    {
        $mapper = $this->getContainer()->get('messenger.mappers.chat');

        $data = [
            'author_name' => 'Test author name',
            'message'     => '<script>alert("!")</script><div>This is the test message with script</div>',
            'is_user'     => true,
        ];

        $result = $mapper->createChatMessage($data);
        $this->assertEquals(true, $result->getIsUser());
        $this->assertEquals('<div>This is the test message with script</div>', $result->getContent());
        $this->assertEquals(0, $result->getAuthorId());
        $this->assertEquals('Test author name', $result->getPersonName());
        $this->assertEquals(0, $result->getConversationId());
        $this->assertEquals(true, $result->isHtml());
        $this->assertEquals(true, $result->getIsUser());
        $this->assertEquals(false, $result->getIsSys());
        $this->assertEquals(null, $result->getId());
    }

    public function testMapMessage()
    {
        $mapper = $this->getContainer()->get('messenger.mappers.chat');

        $message = new ChatMessage();
        $message
            ->setOrigin(ChatMessage::ORIGIN_USER)
            ->setIsUser(true)
            ->setPersonName('Test author name')
            ->setContent('Test content')
            ->setIsSys(false)
            ->setIsHtml(true);

        $expectedData = [
            'author_name' => 'Test author name',
            'message'     => 'Test content',
            'is_user'     => true,
            'is_sys'      => false,
            'is_html'     => true,
            'author'      => 0,
            'chat'        => 0,
            'id'          => 0,
        ];

        $result = $mapper->mapMessageToArray($message);
        $this->assertEquals($expectedData, $result);
    }
}
