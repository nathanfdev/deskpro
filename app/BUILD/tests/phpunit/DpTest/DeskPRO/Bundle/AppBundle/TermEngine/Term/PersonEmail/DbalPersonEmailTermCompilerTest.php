<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
            'email' => 'chris.tickner@deskpro.com',
        ]);
        $this->assertWhere($qp, '
                ticket.person_id  IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email = :email) OR  EXISTS(
                  SELECT * FROM
                    tickets_participants tp
                        JOIN
                    people p ON tp.person_id = p.id
                  WHERE
                    p.is_agent = 0 AND p.id  IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email = :email) AND ticket.id = tp.ticket_id
                )
            ');
        $this->assertNoUniqueJoins($qp);
    }

    public function testCompileIsNot()
    {
        $term = new PersonEmailTerm(['email' => 'chris.tickner@deskpro.com'], TermInterface::OP_NOT);
        $qp   = $this->term_compiler->compile($term);

        $this->assertParameters($qp, [
            'email' => 'chris.tickner@deskpro.com',
        ]);
        $this->assertWhere($qp, '
                ticket.person_id NOT IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email = :email) AND NOT EXISTS(
                  SELECT * FROM
                    tickets_participants tp
                        JOIN
                    people p ON tp.person_id = p.id
                  WHERE
                    p.is_agent = 0 AND p.id NOT IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email = :email) AND ticket.id = tp.ticket_id
                )
            ');
        $this->assertNoJoins($qp);
    }
}
