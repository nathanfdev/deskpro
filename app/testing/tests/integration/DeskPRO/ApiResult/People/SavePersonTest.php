<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DpIntegrationTests\DeskPRO\ApiResult\People;

use DpIntegrationTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__.'/../AbstractApiResultTest.php';

class SavePersonTest extends AbstractApiResultTest
{
    public function testCanCreatePerson()
    {
        $builder = $this->getApi()->people->createPersonEditor();

        $builder->setName('Test Person')
            ->setEmail('testperson3'.uniqid().'@test.com')
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
