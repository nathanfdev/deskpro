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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;

class DbalQueryManipulator
{
    /**
     * @var TermEngineExpressionLanguage
     */
    private $expression_language;

    public function __construct(TermEngineExpressionLanguage $expression_language)
    {
        $this->expression_language = $expression_language;
    }

    public function ensureAgentPermissions(DbalCompiledQuery $query, DbalEngineContext $context)
    {
    }

    public function alterPagination(DbalCompiledQuery $query, DbalEngineContext $context)
    {
        if ($page = $context->getPage()) {
            $query->setPage($page);
        }

        if ($per_page = $context->getPerPage()) {
            $query->setLimit($per_page);
        }
    }

    public function alterSortOrder(DbalCompiledQuery $query, DbalEngineContext $context)
    {
        foreach ($context->getOrderBy() as $order => $direction) {
            $query->addOrderBy($order, $direction);
        }
    }

    public function alterGrouping(DbalCompiledQuery $query, DbalEngineContext $context)
    {
    }

    public function alterWhere(DbalCompiledQuery $query, DbalEngineContext $context)
    {
        if ($and_where = $context->getAndWhere()) {
            $query->appendWhere(sprintf('AND (%s)', $and_where));
        }
    }

    public function resolveParameters(DbalCompiledQuery $query, DbalEngineContext $context)
    {
        foreach ($query->getParameters() as $key => $val) {
            $resolved = $this->resolveParam($val, $context);

            // if the resolution changed the value, replace it
            if ($resolved !== $val) {
                $query->replaceParameter($key, $resolved);
            }
        }
    }

    private function resolveParam($val, DbalEngineContext $context)
    {
        if (is_array($val)) {
            $new_val = array();

            foreach ($val as $key => $val) {
                $new_val[$key] = $this->resolveParam($val, $context);
            }

            return $new_val;
        }

        if ($val instanceof TermEngineExpression) {
            return $this->evalExpression($val, $context);
        }

        return $val;
    }

    private function evalExpression(TermEngineExpression $val, DbalEngineContext $context)
    {
        return $this->expression_language->evaluate(
            (string)$val,
            array(
                'agent' => $context->getAgent()
            )
        );
    }
}
