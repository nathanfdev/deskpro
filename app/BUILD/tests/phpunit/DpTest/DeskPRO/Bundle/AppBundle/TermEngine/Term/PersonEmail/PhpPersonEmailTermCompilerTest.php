<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail\PersonEmailTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail\PhpPersonEmailTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

/**
 * Class PhpPersonEmailTermCompilerTest.
 */
class PhpPersonEmailTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpPersonEmailTermCompiler
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
        $ticket = $this->prophesize(Ticket::class);
        if (!$email) {
            $email = null;
        }
        $ticket->getId()->willReturn(5);
        $ticket->getPersonEmailAddress()->willReturn($email);

        return $ticket;
    }
}
