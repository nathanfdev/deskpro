<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail\DbalPersonEmailTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail\PersonEmailTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalPersonEmailTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalPersonEmailTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.person_email');
    }

    public function testCompileIs()
    {
        $term = new PersonEmailTerm(['email' => 'chris.tickner@deskpro.com']);
        $qp   = $this->term_compiler->compile($term);

        $this->assertParameters($qp, [
            'email' => ['chris.tickner@deskpro.com'],
        ]);
        $this->assertWhere($qp, '
                ticket.person_id  IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email IN(:email)) OR  EXISTS(
                  SELECT * FROM
                    tickets_participants tp
                        JOIN
                    people p ON tp.person_id = p.id
                  WHERE
                    p.is_agent = 0 AND p.id  IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email IN(:email)) AND ticket.id = tp.ticket_id
                )
            ');
        $this->assertNoUniqueJoins($qp);
    }

    public function testCompileIsNot()
    {
        $term = new PersonEmailTerm(['email' => 'chris.tickner@deskpro.com'], TermInterface::OP_NOT);
        $qp   = $this->term_compiler->compile($term);

        $this->assertParameters($qp, [
            'email' => ['chris.tickner@deskpro.com'],
        ]);
        $this->assertWhere($qp, '
                ticket.person_id NOT IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email IN(:email)) AND NOT EXISTS(
                  SELECT * FROM
                    tickets_participants tp
                        JOIN
                    people p ON tp.person_id = p.id
                  WHERE
                    p.is_agent = 0 AND p.id NOT IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email IN(:email)) AND ticket.id = tp.ticket_id
                )
            ');
        $this->assertNoJoins($qp);
    }
}
