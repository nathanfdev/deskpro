<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage\TicketLanguageTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

/**
 * Class PhpTicketLanguageTermCompilerTest.
 */
class PhpTicketLanguageTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage\PhpTicketLanguageTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_language');
    }

    public function testCompileIs()
    {
        $term     = new TicketLanguageTerm(['language' => 1]);
        $phpCheck = $this->term_compiler->compile($term);
        $ticket   = $this->createTicketProphecy();

        $ticket->getLanguage()->willReturn((object) ['id' => 1, 'lang_code' => 'eng']);
        $this->assertTicketCheck(
            $phpCheck,
            true,
            $ticket
        );

        $ticket->getLanguage()->willReturn((object) ['id' => 2, 'lang_code' => 'eng']);
        $this->assertTicketCheck(
            $phpCheck,
            false,
            $ticket
        );
    }

    public function testCompileIsNot()
    {
        $term = new TicketLanguageTerm(['language' => '1'], TermInterface::OP_NOT);

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->getLanguage()->willReturn((object) ['id' => 1, 'lang_code' => 'eng']);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
        $ticket->getLanguage()->willReturn((object) ['id' => 2, 'lang_code' => 'eng']);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
    }

    public function testLangCodeCompileIs()
    {
        $term = new TicketLanguageTerm(['language' => 'eng']);

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->getLanguage()->willReturn((object) ['lang_code' => 'eng', 'id' => 1]);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->getLanguage()->willReturn((object) ['lang_code' => 'ger', 'id' => 1]);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
    }

    public function testLangCodeCompileIsNot()
    {
        $term = new TicketLanguageTerm(['language' => 'ger'], TermInterface::OP_NOT);

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->getLanguage()->willReturn((object) ['lang_code' => 'ger', 'id' => 1]);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
        $ticket->getLanguage()->willReturn((object) ['lang_code' => 'eng', 'id' => 1]);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
    }

    protected function createTicketProphecy()
    {
        $ticket = $this->prophesize(Ticket::class);
        $ticket->getId()->willReturn(4);

        return $ticket;
    }
}
