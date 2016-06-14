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

namespace DpIntegrationTests\DeskPRO\ApiResult\Tickets;

use DpIntegrationTests\DeskPRO\ApiResult\AbstractApiResultTest;

require_once __DIR__.'/../AbstractApiResultTest.php';

class DeleteTicketTest extends AbstractApiResultTest
{
    public function testCanDeleteTicket()
    {
        $randomSubject = md5(time());

        $builder = $this->getApi()->tickets->createBuilder();

        $personBuilder = $this->getApi()->people->createPersonEditor();

        $personBuilder->setId(1);

        $builder->setSubject($randomSubject)
            ->setMessage('Test Ticket created for testing')
            ->setCreatedBy($personBuilder);

        $result = $this->getApi()->tickets->save($builder);

        $this->assertEquals('201', $result->getResponseCode());

        $data = $result->getData();

        $this->assertArrayHasKey('ticket_id', $data);

        $newTicketId = $data['ticket_id'];

        $result = $this->getApi()->tickets->delete($newTicketId);

        $this->assertEquals('200', $result->getResponseCode());

        $result = $this->getApi()->tickets->findById($newTicketId);

        $data = $result->getData();

        $this->assertArrayHasKey('ticket', $data);

        $retrievedTicketArray = $data['ticket'];

        $this->assertEquals('hidden', $retrievedTicketArray['status']);
        $this->assertEquals('deleted', $retrievedTicketArray['hidden_status']);
    }
}
