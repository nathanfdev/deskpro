<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

$WIDGETS = [];

//----------------------------------------------------------------------------------------------------------------------
// ROW 1
//----------------------------------------------------------------------------------------------------------------------

$w              = [];
$w['title']     = 'New Matters';
$w['statTitle'] = 'Simple count of matters created this month';
$w['id']        = 'mp_agent_new_matters_month';
$w['size']      = '1:1';
$w['pos']       = '0:0';
$w['type']      = 'simple_stat';
$w['query']     = <<<'QUERY'
    SELECT DPQL_COUNT() as 'stat_value', 'created this month' as 'stat_description' 
    FROM tickets WHERE tickets.date_created = %THIS_MONTH%
QUERY;

$WIDGETS[] = $w;

//----------

$w              = [];
$w['title']     = 'Matters Due (7d)';
$w['statTitle'] = 'Simple count of matters due within 7 days';
$w['id']        = 'mp_agent_due_matters_7d';
$w['size']      = '2:1';
$w['pos']       = '0:1';
$w['type']      = 'simple_stat';
$w['query']     = <<<'QUERY'
    SELECT DPQL_COUNT() as 'stat_value', 'due within 7 days' as 'stat_description' 
    FROM tickets WHERE tickets.date_created = %THIS_YEAR%
QUERY;

$WIDGETS[] = $w;

//----------

$w              = [];
$w['title']     = 'Matters Due (30d)';
$w['statTitle'] = 'Simple count of matters due within 30 days';
$w['id']        = 'mp_agent_due_matters_30d';
$w['size']      = '2:1';
$w['pos']       = '0:3';
$w['type']      = 'simple_stat';
$w['query']     = <<<'QUERY'
    SELECT DPQL_COUNT() as 'stat_value', 'due within 30 days' as 'stat_description' 
    FROM tickets WHERE tickets.date_created = %THIS_YEAR%
QUERY;

$WIDGETS[] = $w;

//----------

$w              = [];
$w['title']     = 'Overdue Matters';
$w['statTitle'] = 'Simple count of overdue matters';
$w['id']        = 'mp_agent_overdue_matters';
$w['size']      = '1:1';
$w['pos']       = '0:5';
$w['type']      = 'simple_stat';
$w['query']     = <<<'QUERY'
    SELECT DPQL_COUNT() as 'stat_value', 'overdue' as 'stat_description' 
    FROM tickets WHERE tickets.date_created = %THIS_YEAR%
QUERY;

$WIDGETS[] = $w;

//----------

$w              = [];
$w['title']     = 'Expiring Contracts';
$w['statTitle'] = 'Simple count of contracts expiring due within 12 months';
$w['id']        = 'mp_agent_contracts_expiring_12m';
$w['size']      = '2:1';
$w['pos']       = '0:6';
$w['type']      = 'simple_stat';
$w['query']     = <<<'QUERY'
    SELECT DPQL_COUNT() as 'stat_value', 'expiring within 12 months' as 'stat_description' 
    FROM tickets WHERE tickets.date_created = %THIS_YEAR%
QUERY;

$WIDGETS[] = $w;

return $WIDGETS;
