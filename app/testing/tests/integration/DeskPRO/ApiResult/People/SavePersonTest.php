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
			->setEmail('testperson3@test.com')
			->setPassword('password');
		
		$result = $this->getApi()->people->save($builder);
		
		$this->assertEquals('201', $result->getResponseCode());
		
		$data = $result->getData();
		
		$this->assertArrayHasKey('id', $data);
		
		$newPersonId = $data['id'];
		
		$this->getApi()->people->deleteById($newPersonId);
	}
}