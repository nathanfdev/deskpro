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

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\QueryCondition;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\QueryConditionGroup;
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
     * These are "compare" functions because they are used as the target of an operator. This is
     * different from value functions (defined on the value resolver) that only return a literal
     * value.
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
     *     TicketFunctionCallDef::create()
     *         ->setName('myFunc')
     *         ->setMatchFn('matchMyFunc')
     *         ->setQueryBuilderFn('qbMyFunc')
     *         ->setOperators(Query::TERM_HAS)
     *         ->setFields('foo', 'tickets.agent)
     * ];
     * </code>
     *
     * The signatures for the functions:
     *
     * <code>
     * matchMyFunc(string $fieldId, string $operator, array $params, TicketModel $ticketModel, Context $context, Term $term)
     * qbMyFunc(string $fieldId, string $operator, array $params, Context $context, Term $term)
     * </code>
     *
     * @return FunctionCompareDef[]
     */
    public function getCompareFunctions();

    /**
     * @param string      $fieldId     The field the term is based on
     * @param string      $operator    The term operator
     * @param OptValue    $options     The value for the term
     * @param TicketModel $ticketModel The current ticket model
     * @param Context     $context     The current context
     * @param Term        $term        The raw term from which fieldId, operator, and options were read from
     *
     * @return bool
     */
    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term);

    /**
     * @param string   $fieldId  The field the term is based on
     * @param string   $operator The term operator
     * @param OptValue $options  The value for the term
     * @param Context  $context  The current context
     * @param Term     $term     The raw term from which fieldId, operator, and options were read from
     *
     * @return QueryCondition|QueryConditionGroup|QueryCondition[]|QueryConditionGroup[]
     */
    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term);
}
