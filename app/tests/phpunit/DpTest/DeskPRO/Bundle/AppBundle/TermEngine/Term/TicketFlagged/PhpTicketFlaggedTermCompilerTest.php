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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged;

use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged\TicketFlaggedTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;

class PhpTicketFlaggedTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;
    protected $helper_pool;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_flagged');
        $this->helper_pool = $this->get('term_engine.php.helper_pool');
    }

    public function testCompileIs()
    {
        $ticket = $this->createTicketProphecy();

        $helper = (object)array(
            'getFlags' => function($ticket, $agent_id) {
                die('koin');
                return array(
                    'blue',
                );
            }
        );
        
        $term = new TicketFlaggedTerm(
            array(
                'flag' => 'blue',
            )
        );

        $php_check = $this->term_compiler->compile($term);

        $helper = \Mockery::mock('stdClass')
            ->shouldReceive('getFlags')
            ->andReturn(array('blue'))
            ->mock();

        $my_helper_pool = $this->get('term_engine.php.helper_pool');
        $this->helper_pool = \Mockery::mock($my_helper_pool)
            ->shouldReceive('getHelper')
            ->with('agent')
            ->andReturn($helper)
            ->shouldReceive('getHelper')
            ->with('method_check')
            ->passthru()
            ->mock();

        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
    }
    /*
    public function testCompileIsNot()
    {
        $term = new TicketFlaggedTerm(
            array(
                'flag' => 'blue'
            ),
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->getLanguage()->willReturn((object)array('lang_code' => 'eng'));
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
        $ticket->getLanguage()->willReturn((object)array('lang_code' => 'ger'));
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
    }
    */

    protected function createTicketProphecy()
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(4);

        return $ticket;
    }
}
