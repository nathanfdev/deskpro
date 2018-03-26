<?php

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
