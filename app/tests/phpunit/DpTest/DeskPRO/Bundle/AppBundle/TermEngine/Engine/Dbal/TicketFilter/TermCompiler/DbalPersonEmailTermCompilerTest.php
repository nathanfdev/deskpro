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

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmailTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalPersonEmailTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    public function testCompileIs()
    {
        $term = new PersonEmailTerm(
            array(
                'email' => 'chris.tickner@deskpro.com'
            )
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'people_emails.email = :email_0',
            array(
                'email_0' => 'chris.tickner@deskpro.com'
            ),
            'LEFT JOIN people_emails ON (ticket.person_id = people_emails.person_id)'
        );
    }

    public function testCompileIsNort()
    {
        $term = new PersonEmailTerm(
            array(
                'email' => 'chris.tickner@deskpro.com'
            ),
            TermInterface::OP_NOT
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'people_emails_0.id IS NULL',
            array(
                'email_0' => 'chris.tickner@deskpro.com'
            ),
            null,
            'LEFT JOIN people_emails people_emails_0 ON (ticket.person_id = people_emails_0.person_id AND people_emails_0.email = :email_0)'
        );
    }
}
