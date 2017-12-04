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
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;

/**
 * A terms handler has two jobs:.
 *
 * - It matches in-memory ticket models -- referred to as matching
 * - And it also compiles a term into SQL -- referred to as querying
 *
 * Every handler can handle multiple terms. The way you split up term
 * handlers is largely arbitrary; it's just a way to group related
 * terms together to keep from any single class becoming too large.
 */
interface TermsHandlerInterface
{
    /**
     * Return an array of fieds this term handler handles.
     *
     * @return string[]
     */
    public function getHandledFields();

    /**
     * Functions that handle calls specially for specific operators. E.g.: foo HAS myFunc().
     *
     * A function needs both a matcher as well as a queryer. The matcher needs to
     * return true/false, and the queryer needs to operate on the query builder
     * to add the required conditions.
     *
     * Note that when a function is encountered, the methods defiend on the function def
     * are called and NOT the default doesTicketMatch or buildQuery functions.
     *
     * The format for each entry is is a TicketFunctionCallDef. You can use the bilder for
     * a fluid interface.
     *
     * <code>
     * $matchFns = [
     *     TicketFunctionCallDef::build()
     *         ->setName('myFunc')
     *         ->setMatchFn('matchMyFunc')
     *         ->setQueryBuilderFn('qbMyFunc')
     *         ->setOperators(Query::TERM_HAS)
     *         ->setFields('foo', 'tickets.agent)
     *         ->getDef()
     * ];
     * </code>
     *
     * The signatures for the functions:
     *
     * <code>
     * matchMyFunc(array $params, Term $term, TicketModel $ticketModel, MatcherContext $matcherContext)
     * qbMyFunc(array $params, QueryBuilder $qb, Term $term, QueryContext $queryContext)
     * </code>
     *
     * @return TermFunctionCallDef[]
     */
    public function getFunctions();

    /**
     * @param Term           $term
     * @param TicketModel    $ticketModel
     * @param MatcherContext $valueResolver
     *
     * @return bool
     */
    public function doesTicketMatch(Term $term, TicketModel $ticketModel, MatcherContext $valueResolver);

    /**
     * @param QueryBuilder $qb
     * @param Term         $term
     * @param QueryContext $queryContext
     */
    public function buildQuery(QueryBuilder $qb, Term $term,  QueryContext $queryContext);
}
