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

use DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalDepartmentTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    public function testCompileIs()
    {
        $term = new DepartmentTerm(
            array(
                'department_ids' => array(1, 2, 15)
            )
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.department_id IN (:department_ids_0)',
            null,
            array(
                'department_ids_0' => array(1, 2, 15),
            )
        );
    }

    public function testCompileIsNort()
    {
        $term = new DepartmentTerm(
            array(
                'department_ids' => array(1, 2, 15)
            ),
            TermInterface::OP_NOT
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.department_id NOT IN (:department_ids_0)',
            null,
            array(
                'department_ids_0' => array(1, 2, 15),
            )
        );
    }
}
