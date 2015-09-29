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
        return require 'Data'.DIRECTORY_SEPARATOR.'ExpectedPerson.php';
    }

    protected function _assertPersonsAreEqual($expectedPersonArray, $retrievedPersonArray)
    {
        foreach ($this->getDateTimeFields('person') as $field) {
            $this->assertIsValidDateTime($retrievedPersonArray[$field]);
        }

        foreach ($this->getTimestampFields('person') as $field) {
            $this->assertIsValidTimestamp($retrievedPersonArray[$field]);
        }

        foreach ($this->_getIgnoreKeys('person') as $key) {
            $this->assertArrayHasKey($key, $retrievedPersonArray);
            unset($retrievedPersonArray[$key]);
            unset($expectedPersonArray[$key]);
        }

        $this->assertApiArrayEqual($retrievedPersonArray, $retrievedPersonArray);
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

    /* TODO : fails
    public function testCanFindConfirmedAgentsOnly()
    {
        $criteria = $this->getApi()->people->createCriteria();

        $criteria->agentConfirmedOnly();

        $result = $this->getApi()->people->find($criteria);

        $this->assertEquals('200', $result->getResponseCode());

        $data = $result->getData();

        $this->assertArrayHasKey('people', $data);

        $this->assertNotEmpty($data['people']);

        $keys = array_keys($data['people']);

        $this->assertArrayHasKey('is_agent', $data['people'][$keys[0]]);

        $this->assertTrue($data['people'][$keys[0]]['is_agent']);
        $this->assertTrue($data['people'][$keys[0]]['is_agent_confirmed']);
    }
    */

    public function testCanFindByUserGroup()
    {
        $testUserGroup = 4;

        $criteria = $this->getApi()->people->createCriteria();

        $criteria->addUserGroup($testUserGroup);

        $result = $this->getApi()->people->find($criteria);

        $this->assertEquals('200', $result->getResponseCode());

        $data = $result->getData();

        $this->assertArrayHasKey('people', $data);

        $this->assertNotEmpty($data['people']);

        $keys = array_keys($data['people']);

        $person = $data['people'][$keys[0]];

        $this->assertArrayHasKey('usergroups', $person);

        $this->assertNotEmpty($person['usergroups']);

        $keys = array_keys($person['usergroups']);

        $this->assertEquals($testUserGroup, $person['usergroups'][$keys[0]]['id']);
    }
}
