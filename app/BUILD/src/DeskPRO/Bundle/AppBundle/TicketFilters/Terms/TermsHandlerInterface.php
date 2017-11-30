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
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;

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
     * These functions are called and must return true/false to determine the match.
     * If a function matches, doesTicketMatch does NOT get called for that term.
     *
     * This is different from other callable functions that just return a value like any other,
     * which are then just used as-is by normal comparators.
     *
     * The format for each entry is:
     *
     * <code>
     * $matchFns = [
     *     ['name' => 'myFunc', 'method' => 'classMethodName', 'operators' => [Query::OP_HAS]]
     * ];
     * </code>
     *
     * @return array
     */
    public function getMatchFunctions();

    /**
     * @param Term           $term
     * @param TicketModel    $ticketModel
     * @param MatcherContext $valueResolver
     *
     * @return bool
     */
    public function doesTicketMatch(Term $term, TicketModel $ticketModel, MatcherContext $valueResolver);
}
