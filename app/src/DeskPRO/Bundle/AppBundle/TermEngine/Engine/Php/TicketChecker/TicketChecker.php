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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermCompilerHelperPool;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;

class TicketChecker implements PhpTicketCheckerInterface
{
    /**
     * @var PhpCheck
     */
    private $php_check;

    /**
     * @var TermEngineContext
     */
    private $context;

    /**
     * @var TermEngineExpressionLanguage
     */
    private $expression_language;

    /**
     * @var TermCompilerHelperPool
     */
    private $helper_pool;

    public function __construct(
        PhpCheck $php_check,
        TermEngineContext $context,
        TermEngineExpressionLanguage $expression_language,
        TermCompilerHelperPool $helper_pool
    )
    {
        $this->php_check = clone $php_check; // use a clone
        $this->context = $context;
        $this->expression_language = $expression_language;
        $this->helper_pool = $helper_pool;
    }

    /**
     * @param Ticket $ticket
     * @return bool
     */
    public function isTicketMatch(Ticket $ticket)
    {
        $this->php_check->freezeVariableNames();
        return $this->expression_language->evaluate(
            $this->php_check->getExpression(),
            array_merge(
                $this->php_check->getVariables(),
                array(
                    'agent' => $this->context->getAgent(),
                    'ticket' => $ticket,
                    'helper_pool' => $this->helper_pool
                )
            )
        );
    }
}
