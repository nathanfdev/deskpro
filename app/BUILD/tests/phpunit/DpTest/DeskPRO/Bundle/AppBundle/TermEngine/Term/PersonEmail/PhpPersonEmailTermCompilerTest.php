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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail\PersonEmailTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpPersonEmailTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.person_email');
    }

    public function testCompileIs()
    {
        $term = new PersonEmailTerm(
            [
                'email' => 'chris.tickner@deskpro.com',
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy('chris.tickner@deskpro.com'));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy('chris.tickner+test@deskpro.com'));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy('chris.tickner@deskpro.comm'));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy());
    }

    public function testCompileIsNot()
    {
        $term = new PersonEmailTerm(
            [
                'email' => 'chris.tickner@deskpro.com',
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy('chris.tickner@deskpro.com'));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy('chris.tickner+test@deskpro.com'));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy('chris.tickner@deskpro.comm'));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy());
    }

    protected function createTicketProphecy($email = null)
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        if (!$email) {
            $email = null;
        }
        $ticket->getId()->willReturn(5);
        $ticket->getPersonEmailAddress()->willReturn($email);

        return $ticket;
    }
}
