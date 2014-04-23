<?php

namespace DpUnitTests\DeskPRO\ApiResult\Tickets;

use DpUnitTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__ . '/../AbstractApiResultTest.php';

class DeletePersonTest extends AbstractApiResultTest
{
	public function testCanDeletePerson()
	{
		$builder = $this->getApi()->people->createPersonEditor();
		
		$builder->setName('Test Person')
			->setEmail('testperson@test.com')
			->setPassword('password');
		
		$result = $this->getApi()->people->save($builder);
		
		$this->assertEquals('201', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('id', $data);
		
		$newPersonId = $data['id'];
		
		$result = $this->getApi()->people->deleteById($newPersonId);
		
		$this->assertEquals('200', $result->getResponseCode());
		
		$result = $this->getApi()->people->findById($newPersonId);
		
		$this->assertEquals('404', $result->getResponseCode());
	}
}