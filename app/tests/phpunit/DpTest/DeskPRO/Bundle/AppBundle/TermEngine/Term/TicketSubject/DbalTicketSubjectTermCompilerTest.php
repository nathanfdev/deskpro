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
        $params = array('subject' => array('test subject'));
        $check  = array('string0' => $params['subject'][0]);
        $term   = new TicketSubjectTerm($params);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject = :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleNotCase()
    {
        $params = array('subject' => array('test subject'));
        $check  = array('string0' => $params['subject'][0]);
        $term   = new TicketSubjectTerm($params, TermInterface::OP_NOT);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject != :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleHasCase()
    {
        $params = array('subject' => array('test subject'));
        $check  = array('string0' => '%'.$params['subject'][0].'%');
        $term   = new TicketSubjectTerm($params, TermInterface::OP_HAS);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject LIKE :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleNotHasCase()
    {
        $params = array('subject' => array('test subject'));
        $check  = array('string0' => '%'.$params['subject'][0].'%');
        $term   = new TicketSubjectTerm($params, TermInterface::OP_NOT_HAS);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject NOT LIKE :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testWildcardPrefix()
    {
        $params = array('subject' => array('test subject'), 'wildcard_prefix' => true);
        $check  = array('string0' => '%'.$params['subject'][0]);
        $term   = new TicketSubjectTerm($params, TermInterface::OP_IS);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject LIKE :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testWildcardPostfix()
    {
        $params = array('subject' => array('test subject'), 'wildcard_postfix' => true);
        $check  = array('string0' => $params['subject'][0].'%');
        $term   = new TicketSubjectTerm($params, TermInterface::OP_IS);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject LIKE :string0');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testMultipleStringsWithIsOperator()
    {
        $params = array('subject' => array('test subject', 'test subject2'));
        $check  = array('string0' => $params['subject'][0], 'string1' => $params['subject'][1]);
        $term   = new TicketSubjectTerm($params, TermInterface::OP_IS);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject = :string0 OR ticket.subject = :string1');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testMultipleStringsWithNotOperator()
    {
        $params = array('subject' => array('test subject', 'test subject2'));
        $check  = array('string0' => $params['subject'][0], 'string1' => $params['subject'][1]);
        $term   = new TicketSubjectTerm($params, TermInterface::OP_NOT);

        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $check);
        $this->assertWhere($query_part, 'ticket.subject != :string0 AND ticket.subject != :string1');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
