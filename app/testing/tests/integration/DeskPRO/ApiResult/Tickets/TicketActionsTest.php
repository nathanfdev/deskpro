<?php

namespace DpUnitTests\DeskPRO\ApiResult\Tickets;

use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class TicketActionsTest extends AbstractApiResultTest
{
	public function testCanClaimTicket()
	{
		$testTicketId = 1;
		
		$result = $this->getApi()->tickets->findById($testTicketId);
		
		$data = $result->getData();
		
		$this->assertEmpty($data['ticket']['agent']);
		
		$result = $this->getApi()->tickets->assignToMe($testTicketId);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$result = $this->getApi()->tickets->findById($testTicketId);
		
		$data = $result->getData();
		
		$this->assertNotEmpty($data['ticket']['agent']);
		
		$this->assertEquals(1, $data['ticket']['agent']['id']);
		
		$builder = $this->getApi()->tickets->createBuilder();
		
		$builder->setId($testTicketId)->assignToAgent(0);
		
		$this->getApi()->tickets->save($builder);
	}

	public function testCanGetMessages()
	{
		$testTicketId = 1;
		
		$result = $this->getApi()->tickets->getMessages($testTicketId);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('messages', $data);
		
		$this->assertGreaterThanOrEqual(1, count($data['messages']));
		
		$retrievedTicketMessageArray = $data['messages'][0];
		
		$expectedTicketMessage = $this->_getExpectedTicketMessage();

		$this->assertMessagesAreEqual($retrievedTicketMessageArray, $expectedTicketMessage);
	}
	
	public function testCanCreateMessage()
	{
		$testTicketId = 1;
		
		$testTicketMessageText = md5(time());
		
		$result = $this->getApi()->tickets->getMessages($testTicketId);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('messages', $data);
		
		$oldNoOfMessages = count($data['messages']);
		
		$result = $this->getApi()->tickets->createMessage($testTicketId, $testTicketMessageText);
		
		$this->assertEquals('201', $result->getResponseCode());
		
		$data = $result->getData();
		
		$newTicketMessageId = $data['message_id'];
		
		$result = $this->getApi()->tickets->getMessages($testTicketId);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('messages', $data);
		
		$newNoOfMessages = count($data['messages']);
		
		$this->assertEquals($oldNoOfMessages + 1, $newNoOfMessages);
		
		$this->assertEquals($testTicketMessageText, $data['messages'][$oldNoOfMessages]['message']);
	}
	
	public function testCanGetMessage()
	{
		$testTicketId = 1;
		
		$testTicketMessageId = 1;
		
		$result = $this->getApi()->tickets->getMessage($testTicketId, $testTicketMessageId);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('message', $data);
		
		$this->assertMessagesAreEqual($this->_getExpectedTicketMessage(), $data['message']);
	}
	
	public function testCanGetTicketSlas()
	{
		$testTicketId = 1;
		
		$result = $this->getApi()->tickets->getTicketSlas($testTicketId);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('ticket_slas', $data);
	}
	
	protected function _getExpectedTicketMessage()
	{
		return require 'Data' . DIRECTORY_SEPARATOR . 'ExpectedTicketMessage.php';
	}
	
	protected function assertMessagesAreEqual($retrievedTicketMessageArray, $expectedTicketMessage)
	{
		foreach ($this->getDateTimeFields('ticket_message') as $field) {
			$this->assertIsValidDateTime($retrievedTicketMessageArray[$field]);
		}

		foreach ($this->getDateTimeFields('person') as $field) {
			$this->assertIsValidDateTime($retrievedTicketMessageArray['person'][$field]);
		}

		foreach ($this->getTimestampFields('ticket_message') as $field) {
			$this->assertIsValidTimestamp($retrievedTicketMessageArray[$field], "$field");
		}

		foreach ($this->getTimestampFields('person') as $field) {
			$this->assertIsValidTimestamp($retrievedTicketMessageArray['person'][$field], "person.$field");
		}

		foreach($this->_getIgnoreKeys('person') as $key) {
			$this->assertArrayHasKey($key, $retrievedTicketMessageArray['person']);
			unset($retrievedTicketMessageArray['person'][$key]);
			unset($expectedTicketMessage['person'][$key]);
		}

		foreach ($this->_getIgnoreKeys('ticket_message') as $key) {
			$this->assertArrayHasKey($key, $retrievedTicketMessageArray);
			unset($retrievedTicketMessageArray[$key]);
			unset($expectedTicketMessage[$key]);
		}
		
		return $this->assertEquals($retrievedTicketMessageArray, $expectedTicketMessage);
	}
}