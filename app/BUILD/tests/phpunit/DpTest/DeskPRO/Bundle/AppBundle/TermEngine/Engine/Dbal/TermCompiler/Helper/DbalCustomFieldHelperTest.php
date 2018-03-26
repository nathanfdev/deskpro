<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketRef\DbalTicketRefTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalCustomFieldHelperTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketRefTermCompiler
     */
    protected $helper;

    public function setUp()
    {
        $this->helper = $this->get('term_engine.dbal.custom_field');
    }

    public function testBasic()
    {
        $query_part = $this->helper->buildQueryPart(
            666,
            TermInterface::OP_IS,
            [
                'devil',
                'satan',
            ]
        );

        $this->assertParameters(
            $query_part,
            [
                'input0' => [
                    'devil',
                    'satan',
                ],
            ]
        );

        $this->assertWhere($query_part, 'custom_data_ticket.value IN (:input0)');
    }
}
