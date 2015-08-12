<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

namespace DpTest\DeskPRO\Bundle\PortalBundle\Routing;

use DeskPRO\Bundle\PortalBundle\Routing\ObjectUrlGenerator;

/**
 * DeskPRO
 *
 * @package DeskPRO
 */
class ObjectUrlGeneratorTest extends \DpTest\DeskProTestCase
{
    /**
     * A prophecy of the brand stack
     * @var \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    protected $brand_stack;

    /**
     * A prophecy of the active brand container
     * @var \DeskPRO\Bundle\PortalBundle\Brand\BrandContainer
     */
    protected $active_brand_container;

    /**
     * A prophecy of the portal router
     * @var \DeskPRO\Bundle\PortalBundle\Routing\PortalRouter
     */
    protected $url_generator;

    public function setUp()
    {
        // setup a prophesized brand stack for these tests

        /** @var \DeskPRO\Bundle\PortalBundle\Brand\BrandContainer $brand_container */
        $this->active_brand_container = $this->prophesize('DeskPRO\Bundle\PortalBundle\Brand\BrandContainer');
        /** @var \DeskPRO\Bundle\PortalBundle\Brand\BrandStack $brand_stack */
        $this->brand_stack = $this->prophesize('DeskPRO\Bundle\PortalBundle\Brand\BrandStack');
        $this->brand_stack->getActive()->willReturn($this->active_brand_container);
        $this->url_generator = $this->prophesize('DeskPRO\Bundle\PortalBundle\Routing\PortalRouter');
    }

    /**
     * @param array $settings
     * @return ObjectUrlGenerator
     */
    protected function createObjectUrlGeneratorWithSettings(array $settings)
    {
        $object_url_generator = new ObjectUrlGenerator($this->url_generator->reveal(), $this->brand_stack->reveal());

        foreach ($settings as $name => $value) {
            $this->active_brand_container->getSetting($name, \Prophecy\Argument::any())
                                         ->willReturn($value)
            ;
        }

        return $object_url_generator;
    }

    /**
     * @test
     */
    public function it_uses_the_ticket_getRef_when_use_ref_enabled_to_generate_the_url()
    {
        $object_url_generator = $this->createObjectUrlGeneratorWithSettings(array(
            'core.tickets.use_ref' => 1
        ));

        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getRef()->willReturn('XYZ-123');

        $this->url_generator->generate('portal_tickets_view', array('ticket_ref' => 'XYZ-123'))->willReturn('/tickets/XYZ-123/example');

        $this->assertEquals('/tickets/XYZ-123/example', $object_url_generator->generateUrl($ticket->reveal()));
    }

    /**
     * @test
     */
    public function it_uses_the_ticket_Id_when_use_ref_not_enabled_to_generate_the_url()
    {
        $object_url_generator = $this->createObjectUrlGeneratorWithSettings(array(
            'core.tickets.use_ref' => 0
        ));

        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(2);

        $this->url_generator->generate('portal_tickets_view', array('ticket_ref' => 2))->willReturn('/tickets/2/example');

        $this->assertEquals('/tickets/2/example', $object_url_generator->generateUrl($ticket->reveal()));
    }
}
