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
            'message' => '<script>alert("!")</script><div>This is the test message with script</div>',
            'is_user' => true,
            'origin'  => 'user',
        ];

        $result = $mapper->createChatMessage($data);
        $this->assertEquals(true, $result->getIsUser());
        $this->assertEquals('<div>This is the test message with script</div>', $result->getContent());
        $this->assertEquals(0, $result->getAuthorId());
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
            ->setIsHtml(true)
        ;

        $result = $mapper->mapMessageToArray($message);
        $this->assertEquals('Test author name', $result['name']);
        $this->assertEquals('Test content', $result['message']);
        $this->assertTrue($result['is_user']);
        $this->assertTrue($result['is_html']);
        $this->assertFalse($result['is_sys']);
        $this->assertEquals(ChatMessage::ORIGIN_USER, $result['origin']);
        $this->assertContains('/file.php/avatar/80/default.jpg?size-fit=1', $result['avatar']);
    }
}
