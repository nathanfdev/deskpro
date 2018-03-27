<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Department;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DbalDepartmentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalDepartmentTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalDepartmentTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.department');
    }

    public function testCompileIs()
    {
        $term = new DepartmentTerm(
            [
                'department_ids' => [1, 2, 15],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'ids' => [1, 2, 15],
            ]
        );

        $this->assertWhere($query_part, 'ticket.department_id IN (:ids)');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testCompileIsNort()
    {
        $term = new DepartmentTerm(
            [
                'department_ids' => [1, 2, 15],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'ids' => [1, 2, 15],
            ]
        );

        $this->assertWhere($query_part, 'ticket.department_id NOT IN (:ids)');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
