<?php

namespace DpUnitTests\DeskPRO\ApiResult\Tickets;

use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class SavePersonTest extends AbstractApiResultTest
{
	public function testCanCreatePerson()
	{
		$builder = $this->getApi()->people->createPersonEditor();
		
		$builder->setName('Test Person')
			->setEmail('testperson3' . uniqid() . '@test.com')
			->setPassword('password');
		
		$result = $this->getApi()->people->save($builder);
		
		$this->assertEquals('201', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('id', $data);
		
		$newPersonId = $data['id'];

		$this->getDb()->delete('people', array('id' => $newPersonId));
	}
	
	public function testCanEditPerson()
	{
		$testPersonId = 1;
		
		$testName = 'Test Person';
		
		$result = $this->getApi()->people->findById($testPersonId);
		
		$data = $result->getData();
		
		$oldName = $data['person']['name'];
		
		$builder = $this->getApi()->people->createPersonEditor();
		
		$builder->setId($testPersonId)
			->setName($testName);
		
		$result = $this->getApi()->people->save($builder);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$result = $this->getApi()->people->findById($testPersonId);
		
		$data = $result->getData();
		
		$newName = $data['person']['name'];
		
		$this->assertEquals($testName, $newName);
		
		$builder = $this->getApi()->people->createPersonEditor();
		
		$builder->setId($testPersonId)
			->setName($oldName);
		
		$result = $this->getApi()->people->save($builder);
	}
}