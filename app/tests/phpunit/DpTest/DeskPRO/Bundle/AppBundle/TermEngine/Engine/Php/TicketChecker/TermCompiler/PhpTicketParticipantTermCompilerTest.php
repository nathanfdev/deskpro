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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmailTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipantTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class PhpTicketParticipantTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_participant');
    }


    public function testCompileIs()
    {
        $term = new TicketParticipantTerm(
            array(
                'person_ids' => array(4, 9)
            )
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck(
            $php_check,
            true,
            $this->createTicketProphecy(
                array(
                    4 => true,
                    9 => false,
                )
            )
        );
        $this->assertTicketCheck(
            $php_check,
            true,
            $this->createTicketProphecy(
                array(
                    4 => true,
                    9 => true,
                )
            )
        );
        $this->assertTicketCheck(
            $php_check,
            true,
            $this->createTicketProphecy(
                array(
                    4 => true,
                    9 => false,
                )
            )
        );
        $this->assertTicketCheck(
            $php_check,
            false,
            $this->createTicketProphecy(
                array(
                    4 => false,
                    9 => false,
                )
            )
        );
    }

    public function testCompileIsNot()
    {
        $term = new TicketParticipantTerm(
            array(
                'person_ids' => array(4, 9)
            ),
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck(
            $php_check,
            false,
            $this->createTicketProphecy(
                array(
                    4 => true,
                    9 => false,
                )
            )
        );
        $this->assertTicketCheck(
            $php_check,
            false,
            $this->createTicketProphecy(
                array(
                    4 => true,
                    9 => true,
                )
            )
        );
        $this->assertTicketCheck(
            $php_check,
            false,
            $this->createTicketProphecy(
                array(
                    4 => true,
                    9 => false,
                )
            )
        );
        $this->assertTicketCheck(
            $php_check,
            true,
            $this->createTicketProphecy(
                array(
                    4 => false,
                    9 => false,
                )
            )
        );
    }

    public function testCompileIsWithMe()
    {
        $term = new TicketParticipantTerm(
            array(
                'person_ids' => array(4, TicketParticipantTerm::ID_ME)
            )
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck(
            $php_check,
            true,
            $this->createTicketProphecy(
                array(
                    4 => true,
                    2 => false, // me
                )
            )
        );
        $this->assertTicketCheck(
            $php_check,
            true,
            $this->createTicketProphecy(
                array(
                    4 => true,
                    2 => true, // me
                )
            )
        );
        $this->assertTicketCheck(
            $php_check,
            true,
            $this->createTicketProphecy(
                array(
                    4 => true,
                    2 => false, // me
                )
            )
        );
        $this->assertTicketCheck(
            $php_check,
            false,
            $this->createTicketProphecy(
                array(
                    4 => false,
                    2 => false, // me
                )
            )
        );
    }

    public function testCompileIsNoParticipants()
    {
        $term = new TicketParticipantTerm(
            array(
                'person_ids' => array()
            )
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck(
            $php_check,
            true,
            $this->createTicketProphecy(
                array(
                    2 => false, // me
                )
            )
        );
        $this->assertTicketCheck(
            $php_check,
            true,
            $this->createTicketProphecy(
                array()
            )
        );
        $this->assertTicketCheck(
            $php_check,
            false,
            $this->createTicketProphecy(
                array(
                    4 => true, // there is a participant
                )
            )
        );
    }

    protected function createTicketProphecy($participants = array())
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');

        $participant_ids = array();
        foreach ($participants as $id => $is_participant) {
            $ticket->hasParticipantPerson($id)->willReturn((bool)$is_participant);
            if ($is_participant) {
                $participant_ids[] = $id;
            }
        }
        $ticket->getParticipantPeopleIds()->willReturn($participant_ids);

        return $ticket;
    }
}
