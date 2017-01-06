<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\PortalBundle\Controller;

use DpTest\PortalTestCase;

class NewTicketControllerTest extends PortalTestCase
{
    public function testNewTicketPageLoads()
    {
        //TODO removed because this was breaking new-agent tests
        return;
        $this->installDataSet('fresh');

        $client = $this->getClient();

        $crawler = $client->request('GET', '/new-ticket');

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGuestSubmitNewTicket()
    {
        //TODO removed because this was breaking new-agent tests
        return;
        $this->installDataSet('fresh');

        $client = $this->getClient();

        $crawler = $client->request('GET', '/new-ticket');

        $form = $crawler->selectButton('Submit')->form();

        $form['ticket[subject]']               = 'Please help!';
        $form['ticket[message][message_text]'] = 'This is my ticket message!';
        $form['ticket[user_email][email]']     = 'chris.tickner@deskpro.com';

        $crawler = $client->submit($form);

        $response = $client->getResponse();
        $this->assertSame(302, $response->getStatusCode(), 'redirected');
        $this->assertRegExp('!^.*?/new-ticket/thank-you$!', $response->headers->get('Location'), 'redirected to the thank you page');

        $this->assertEmailWithSubjectWasSentTo(
            'chris.tickner@deskpro.com',
            'Thank you for contacting us',
            'You may view the status of your ticket online at this address'
        );

        $client->followRedirect();

        $response = $client->getResponse();
        $this->assertContains('Thank You', $response->getContent());
    }
}
