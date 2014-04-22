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
}