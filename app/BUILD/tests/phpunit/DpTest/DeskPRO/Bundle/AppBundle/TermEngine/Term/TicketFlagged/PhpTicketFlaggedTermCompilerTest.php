<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
