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

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\AddAgentReply;
use Application\DeskPRO\Tickets\ExecutorContext;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;

class AddAgentReplyTest extends DeskProTestCase
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private function getMockContainer()
    {
        if ($this->container) {
            return $this->container;
        }

        $this->container = ContainerMock::create()
            ->withAgentData()
            ->withNullEm()
            ->get();

        return $this->container;
    }

    public function testAdd()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $tok    = sha1(microtime(true).mt_rand(10000, 99999));
        $action = new AddAgentReply(
            [
                'by_assigned_agent' => true,
                'by_agent_id'       => 1,
                'reply_text'        => 'Test reply '.$tok,
                'no_formatter'      => true,
            ]
        );
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertEquals(1, count($ticket->messages));
        $this->assertContains($tok, $ticket->messages[0]->message);
        $this->assertNotNull($ticket->messages[0]->person);
        $this->assertEquals(1, $ticket->messages[0]->person->id);
    }
}
