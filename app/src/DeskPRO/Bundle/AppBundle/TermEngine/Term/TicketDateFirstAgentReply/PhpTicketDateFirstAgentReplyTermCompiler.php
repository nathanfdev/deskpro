<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateFirstAgentReply;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateFirstAgentReply\TicketDateFirstAgentReplyTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class PhpTicketDateFirstAgentReplyTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * Take a term and return a PhpCheck representing the term's query conditions.
     *
     * @param TermInterface $term
     * @return PhpCheck
     */
    protected function doCompile(TermInterface $term)
    {
        $op = $term->getOp();
        $date1 = $term->getOption('date');
        $date2 = $term->getOption('date2');
        
        if (TermInterface::OP_RANGE == $op) {
            return new PhpCheck(
                'ticket.date_first_agent_reply >= :date1 and ticket.date_first_agent_reply <= :date2',
                array(
                    'op' => $op,
                    'date1' => $date1,
                    'date2' => $date2
                )
            );
        } elseif (TermInterface::OP_NOT_RANGE == $op) {
            return new PhpCheck(
                'ticket.date_first_agent_reply < :date1 or ticket.date_first_agent_reply > :date2',
                array(
                    'op' => $op,
                    'date1' => $date1,
                    'date2' => $date2
                )
            );
        } else {
            $real_op = '==';
            switch($op) {
                case TermInterface::OP_IS:
                    $real_op = '==';
                    break;
                case TermInterface::OP_NOT:
                    $real_op = '!=';
                    break;
                case TermInterface::OP_GT:
                    $real_op = '>';
                    break;
                case TermInterface::OP_GTE:
                    $real_op = '>=';
                    break;
                case TermInterface::OP_LT:
                    $real_op = '<';
                    break;
                case TermInterface::OP_LTE:
                    $real_op = '<=';
                    break;
                default:
                    throw new \Exception('Uknown operation: ' . $op);
            }
            
            return new PhpCheck(
                'ticket.date_first_agent_reply '. $real_op .' :date',
                array(
                    'date' => $date1
                )
            );
        }
    }
}
