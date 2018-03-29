<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSubject;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalTicketSubjectTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSubject\TicketSubjectTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketSubjectTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketSubjectTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_subject');
    }

    public function testSimpleIsCase()
    {
        $params = ['subject' => ['test subject']];
        $check  = ['string0' => $params['subject'][0]];
        $term   = new TicketSubjectTerm($params);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject = :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleNotCase()
    {
        $params = ['subject' => ['test subject']];
        $check  = ['string0' => $params['subject'][0]];
        $term   = new TicketSubjectTerm($params, TermInterface::OP_NOT);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject != :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleHasCase()
    {
        $params = ['subject' => ['test subject']];
        $check  = ['string0' => '%'.$params['subject'][0].'%'];
        $term   = new TicketSubjectTerm($params, TermInterface::OP_HAS);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject LIKE :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleNotHasCase()
    {
        $params = ['subject' => ['test subject']];
        $check  = ['string0' => '%'.$params['subject'][0].'%'];
        $term   = new TicketSubjectTerm($params, TermInterface::OP_NOT_HAS);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject NOT LIKE :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testWildcardPrefix()
    {
        $params = ['subject' => ['test subject'], 'wildcard_prefix' => true];
        $check  = ['string0' => '%'.$params['subject'][0]];
        $term   = new TicketSubjectTerm($params, TermInterface::OP_IS);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject LIKE :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testWildcardPostfix()
    {
        $params = ['subject' => ['test subject'], 'wildcard_postfix' => true];
        $check  = ['string0' => $params['subject'][0].'%'];
        $term   = new TicketSubjectTerm($params, TermInterface::OP_IS);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject LIKE :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testMultipleStringsWithIsOperator()
    {
        $params = ['subject' => ['test subject', 'test subject2']];
        $check  = ['string0' => $params['subject'][0], 'string1' => $params['subject'][1]];
        $term   = new TicketSubjectTerm($params, TermInterface::OP_IS);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject = :string0 OR ticket.subject = :string1');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testMultipleStringsWithNotOperator()
    {
        $params = ['subject' => ['test subject', 'test subject2']];
        $check  = ['string0' => $params['subject'][0], 'string1' => $params['subject'][1]];
        $term   = new TicketSubjectTerm($params, TermInterface::OP_NOT);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject != :string0 AND ticket.subject != :string1');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
