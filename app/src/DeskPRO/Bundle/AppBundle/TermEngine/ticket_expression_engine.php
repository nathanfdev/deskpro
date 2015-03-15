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
define('DP_ROOT', __DIR__ . '/../../../../..');
define('DP_WEB_ROOT', __DIR__ . '/../../../../../..');
define('DP_CONFIG_FILE', __DIR__ . '/../../../../../config.php');
define('DP_INTERFACE', 'user');

require_once DP_ROOT . '/sys/preboot.php';
require_once DP_ROOT . '/sys/autoload.php';

// engine construction happens in the container,
// each compiler is just tagged (name: expression_ticket_compiler, and can take any args it wants)
$engine = new \DeskPRO\Bundle\AppBundle\TermEngine\Engine\TicketExpression\TicketExpressionEngine(
    array(
        new \DeskPRO\Bundle\AppBundle\TermEngine\Engine\TicketExpression\Compiler\CompositeTermCompiler(array()),
        new \DeskPRO\Bundle\AppBundle\TermEngine\Engine\TicketExpression\Compiler\AgentTermCompiler(),
        new \DeskPRO\Bundle\AppBundle\TermEngine\Engine\TicketExpression\Compiler\DepartmentTermCompiler()
    )
);

// the admin interface crates terms, and we put them in the right place in the db
$terms = new \DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm();

$agents = new \DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm();
$agents->setOp(\DeskPRO\Bundle\AppBundle\TermEngine\TermInterface::OP_OR);
$agents->addTerm(
    new \DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm(
        array(
            'agent_id' => 5
        )
    )
);
$agents->addTerm(
    new \DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm(
        array(
            'agent_id' => 1
        )
    )
);
$agents->addTerm(
    new \DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm(
        array(
            'agent_id' => 12
        )
    )
);

$departments = new \DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm();
$departments->setOp(\DeskPRO\Bundle\AppBundle\TermEngine\TermInterface::OP_OR);
$departments->addTerm(
    new \DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm(
        array(
            'department_id' => 2
        )
    )
);
$departments->addTerm(
    new \DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm(
        array(
            'department_id' => 3
        )
    )
);


$terms->setOp(\DeskPRO\Bundle\AppBundle\TermEngine\TermInterface::OP_AND);

$terms->addTerm($departments);
$terms->addTerm($agents);

echo $engine->compile($terms);
