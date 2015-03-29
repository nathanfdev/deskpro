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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter;

use DeskPRO\Bundle\AppBundle\Entity\Filter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalQueryManipulator;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use Doctrine\DBAL\Connection;

class DbalTicketFilterEngine extends DbalEngine
{
    /**
     * @var DbalTicketFilterEngineCompiler
     */
    private $compiler;

    /**
     * @var DbalQueryManipulator
     */
    private $query_manipulator;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        DbalTicketFilterEngineCompiler $compiler,
        DbalQueryManipulator $query_manipulator,
        Connection $connection
    )
    {
        $this->compiler = $compiler;
        $this->query_manipulator = $query_manipulator;
        $this->connection = $connection;
    }

    public function evaluate(Filter $filter, DbalEngineContext $context)
    {
        $compiled_query = $this->compiler->compile($filter);

        $this->query_manipulator->alterWhere($compiled_query, $context);
        $this->query_manipulator->ensureAgentPermissions($compiled_query, $context);
        $this->query_manipulator->alterPagination($compiled_query, $context);
        $this->query_manipulator->alterGrouping($compiled_query, $context);
        $this->query_manipulator->alterSortOrder($compiled_query, $context);
        $this->query_manipulator->resolveParameters($compiled_query, $context);

        return new DbalExecutableQuery($compiled_query, $this->connection);
    }
}
