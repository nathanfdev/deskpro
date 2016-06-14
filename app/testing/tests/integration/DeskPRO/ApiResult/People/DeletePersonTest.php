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

class DeletePersonTest extends AbstractApiResultTest
{
    /* TODO Fatal error: Call to undefined method DeskPRO\Api::deletePerson() in /deskpro/www/app/vendor/deskpro/deskpro-api-php/src/Service/People.php on line 65
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

        // ALSO: deleteById is broken
        $result = $this->getApi()->people->deleteById($newPersonId);

        $this->assertEquals('200', $result->getResponseCode());

        $result = $this->getApi()->people->findById($newPersonId);

        $this->assertEquals('404', $result->getResponseCode());
    }
    */
}
