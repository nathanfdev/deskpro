<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged\TicketFlaggedTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketFlaggedTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged\PhpTicketFlaggedTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_flagged');
    }

    /**
     * @param PhpCheck $check
     * @param array    $variables
     *
     * @return \DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermCompilerHelperPool|object
     */
    protected function makeHelperPool(PhpCheck $check, array $variables)
    {
        $color = isset($variables['flag']) ? $variables['flag'] : 'blue';

        $agent_helper = \Mockery::mock('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper\PhpAgentHelper')
            ->shouldReceive('getFlags')
            ->andReturn($color)
            ->shouldReceive('checkContains')
            ->andReturn(true)
            ->mock();

        $helper_pool_real = $this->get('term_engine.php.helper_pool');
        $helper_pool      = \Mockery::mock($helper_pool_real)
            ->shouldReceive('getHelper')->with('agent')->andReturn($agent_helper)
            ->shouldReceive('getHelper')->andReturnUsing([$helper_pool_real, 'getHelper'])
            ->mock();

        return $helper_pool;
    }

    public function testCompileIs()
    {
        $term = new TicketFlaggedTerm([
            'flag' => 'blue',
        ]);

        $php_check = $this->term_compiler->compile($term);
        $ticket    = $this->createTicketProphecy();
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket,
            ['flag' => 'blue']
        );

        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket,
            ['flag' => 'red']
        );
    }

    public function testCompileIsNot()
    {
        $term = new TicketFlaggedTerm([
            'flag' => 'blue',
        ], TermInterface::OP_NOT);

        $php_check = $this->term_compiler->compile($term);
        $ticket    = $this->createTicketProphecy();
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket,
            ['flag' => 'blue']
        );

        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket,
            ['flag' => 'red']
        );
    }

    protected function createTicketProphecy()
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');

        return $ticket;
    }
}
