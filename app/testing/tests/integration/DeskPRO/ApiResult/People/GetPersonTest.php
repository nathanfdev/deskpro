<?php

namespace DpUnitTests\DeskPRO\ApiResult\Tickets;

use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class GetPersonTest extends AbstractApiResultTest
{

	public function testFindById()
	{
		$testPersonId = 1;
		
		$result = $this->getApi()->people->findById($testPersonId);
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('person', $data);
		
		$this->_assertPersonsAreEqual($this->_getExpectedPerson(), $data['person']);
	}
	
	protected function _getExpectedPerson()
	{
		return require 'Data' . DIRECTORY_SEPARATOR . 'ExpectedPerson.php';
	}
	
	protected function _assertPersonsAreEqual($expectedPersonArray, $retrievedPersonArray)
	{
		foreach ($this->getDateTimeFields('person') as $field) {
			$this->assertIsValidDateTime($retrievedPersonArray[$field]);
		}
		
		foreach ($this->getTimestampFields('person') as $field) {
			$this->assertIsValidTimestamp($retrievedPersonArray[$field]);
		}
		
		foreach($this->_getIgnoreKeys('person') as $key) {
			$this->assertArrayHasKey($key, $retrievedPersonArray);
			unset($retrievedPersonArray[$key]);
			unset($expectedPersonArray[$key]);
		}
		
		return $this->assertEquals($retrievedPersonArray, $retrievedPersonArray);
	}
	
	public function testCanFindByAgentGroup()
	{
		$allPermissionGroup = 3;
		
		$criteria = $this->getApi()->people->createCriteria();
		
		$criteria->addAgentTeam($allPermissionGroup);
		
		$result = $this->getApi()->people->find($criteria);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('people', $data);
	}
	
	public function testCanFindAgentsOnly()
	{
		$criteria = $this->getApi()->people->createCriteria();
		
		$criteria->agentsOnly();
		
		$result = $this->getApi()->people->find($criteria);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('people', $data);
		
		$this->assertNotEmpty($data['people']);
		
		$keys = array_keys($data['people']);
		
		$this->assertArrayHasKey('is_agent', $data['people'][$keys[0]]);
		
		$this->assertTrue($data['people'][$keys[0]]['is_agent']);
	}
}