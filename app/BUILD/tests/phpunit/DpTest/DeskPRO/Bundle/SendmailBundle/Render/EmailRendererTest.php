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

namespace DpTest\SendmailBundle\Render;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\SendmailBundle\Render\EmailRenderer;
use DpTest\SendmailTestCase;

class EmailRendererTest extends SendmailTestCase
{
    /**
     * @var EmailRenderer
     */
    private $renderer;
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->renderer = $this->get('email.email_renderer');
    }

    public function testGetStructure()
    {
        $person = new Person();

        $ticket = new Ticket();
        $ticket->setSubject('subject');

        $this->getEntityManager()->persist($person);
        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();
        $ticket->setPerson($person);

        $message = new TicketMessage();
        $message->setPerson($person);
        $ticket->addMessage($message);

        $this->getEntityManager()->persist($message);
        $this->getEntityManager()->flush();

        $viewModel = $this->get('email.user_viewmodel_factory')
            ->createTicketReplyByAgentModel(
                $ticket,
                $message
            );
        $structure = $this->renderer->getStructure($viewModel);
        print_r($structure);
        die();
        $this->assertEquals([], $structure);
    }
}
