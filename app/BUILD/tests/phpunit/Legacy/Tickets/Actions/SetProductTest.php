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
use Application\DeskPRO\Tickets\Actions\SetProduct;
use Application\DeskPRO\Tickets\ExecutorContext;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;

class SetProductTest extends DeskProTestCase
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
        $this->container = ContainerMock::create()->withProducts()->get();

        return $this->container;
    }

    public function testSet()
    {
        $ticket          = new Ticket();
        $ticket->product = $this->getMockContainer()->getProducts()->getById(1);
        $exec            = new ExecutorContext();

        $action = new SetProduct(['product_id' => 55]);
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertInstanceOf('Application\\DeskPRO\\Entity\\Product', $ticket->product);
        $this->assertEquals(55, $ticket->product->id);
    }

    public function testSetNull()
    {
        $ticket          = new Ticket();
        $ticket->product = $this->getMockContainer()->getProducts()->getById(1);
        $exec            = new ExecutorContext();

        $action = new SetProduct(['product_id' => 0]);
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->product);
    }

    public function testNoop()
    {
        $ticket          = new Ticket();
        $ticket->product = $this->getMockContainer()->getProducts()->getById(55);

        $exec = new ExecutorContext();

        $action = new SetProduct(['product_id' => 55]);
        $action->setContainer($this->container);

        $this->assertTrue($action->isNoop($ticket, $exec));
    }

    public function testInvalid()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new SetProduct(['product_id' => 200]);
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->product);
    }
}
