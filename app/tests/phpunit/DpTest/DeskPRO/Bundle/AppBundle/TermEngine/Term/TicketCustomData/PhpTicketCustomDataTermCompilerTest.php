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

/**
 * DeskPRO.
 */
namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCustomData;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler\PhpTicketCustomDataTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCustomData\TicketCustomDataTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketCustomDataTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketCustomDataTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_custom_data');
    }

    public function testISwhenCustomDataFieldNotOnTicket()
    {
        $term = new TicketCustomDataTerm(
            array(
                'field_id' => 3,
                'values'   => array(6),
            )
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(5);
        $ticket->hasCustomField(3)->willReturn(false);

        $this->assertTicketCheck($php_check, false, $ticket);
    }

    public function testNOTWhenCustomDataFieldNotOnTicket()
    {
        $term = new TicketCustomDataTerm(
            array(
                'field_id' => 3,
                'values'   => array(6),
            ),
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(5);
        $ticket->hasCustomField(3)->willReturn(false);

        $this->assertTicketCheck($php_check, true, $ticket);
    }

    public function testIScustomFieldValue()
    {
        $term = new TicketCustomDataTerm(
            array(
                'field_id' => 3,
                'values'   => array(6),
            )
        );

        $php_check = $this->term_compiler->compile($term);

        $custom_data = $this->prophesize('Application\DeskPRO\Entity\CustomDataTicket');
        $custom_data->getValue()->willReturn(3); // should fail IS op

        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(5);
        $ticket->hasCustomField(3)->willReturn(true);
        $ticket->getCustomDataForField(3)->willReturn($custom_data);

        $this->assertTicketCheck($php_check, false, $ticket);
    }

    public function testNOTCustomFieldValue()
    {
        $term = new TicketCustomDataTerm(
            array(
                'field_id' => 3,
                'values'   => array(6),
            ),
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $custom_data = $this->prophesize('Application\DeskPRO\Entity\CustomDataTicket');
        $custom_data->getValue()->willReturn(5); // should pass NOT op

        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(5);
        $ticket->hasCustomField(3)->willReturn(true);
        $ticket->getCustomDataForField(3)->willReturn($custom_data);

        $this->assertTicketCheck($php_check, true, $ticket);
    }

    public function testIScustomFieldValues()
    {
        $term = new TicketCustomDataTerm(
            array(
                'field_id' => 3,
                'values'   => array(10, 6),
            )
        );

        $php_check = $this->term_compiler->compile($term);

        $custom_data = $this->prophesize('Application\DeskPRO\Entity\CustomDataTicket');
        $custom_data->getValue()->willReturn(6); // should pass IS op

        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(5);
        $ticket->hasCustomField(3)->willReturn(true);
        $ticket->getCustomDataForField(3)->willReturn($custom_data);

        $this->assertTicketCheck($php_check, true, $ticket);
    }

    public function testNOTCustomFieldValues()
    {
        $term = new TicketCustomDataTerm(
            array(
                'field_id' => 3,
                'values'   => array(10, 6),
            ),
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $custom_data = $this->prophesize('Application\DeskPRO\Entity\CustomDataTicket');
        $custom_data->getValue()->willReturn(10); // should pass NOT op

        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(5);
        $ticket->hasCustomField(3)->willReturn(true);
        $ticket->getCustomDataForField(3)->willReturn($custom_data);

        $this->assertTicketCheck($php_check, false, $ticket);
    }
}
