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

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\MatcherContext;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\QueryBuilder;
use DeskPRO\Bundle\AppBundle\TicketFilters\QueryContext;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueResolver;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\CompareOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\FuncVal;

abstract class AbstractTermsHandler implements TermsHandlerInterface
{
    /**
     * @var ValueResolver
     */
    protected $valueResolver;

    /**
     * AbstractTermsHandler constructor.
     *
     * @param ValueResolver $valueResolver
     */
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }

    /**
     * Check if the current term check is a functioncall we want to handle.
     *
     * @param Term            $term
     * @param string|string[] $expectOp
     * @param string          $expectFn
     *
     * @return bool
     */
    public function isTermFunctionCall(Term $term, $expectOp, $expectFn)
    {
        if (!is_array($expectOp)) {
            $expectOp = [$expectOp];
        }

        return in_array($term->operator->getOperator(), $expectOp, true)
            && $term->options instanceof CompareOpt
            && $term->options->value instanceof FuncVal
            && $term->options->value->name === $expectFn;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [];
    }

    /***
     * @param Term           $term
     * @param TicketModel    $ticketModel
     * @param MatcherContext $matcherContext
     *
     * @return bool
     */
    public function doesTicketMatch(Term $term, TicketModel $ticketModel, MatcherContext $matcherContext)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function buildQuery(QueryBuilder $qb, Term $term, QueryContext $queryContext)
    {
        $qb->andWhere('0');
    }
}
