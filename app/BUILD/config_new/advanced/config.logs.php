<?php

$LOGS_CONFIG = [];

######################################################
# General Application Log                            #
######################################################
# The application log includes a lot of diagnostic   #
# information about any request served by the system.#
#                                                    #
# Change these values to change how much data to add #
# to the log:                                        #
#                                                    #
# - log_level: Which level of logging to save        #
# - log_leve_threshold: When to start saving log     #
#   messages. If no log message is emitted at this   #
#   level or higher, no logs will be saved.          #
#                                                    #
# Default is info/error. This means if an error      #
# happens, the system will save all info messages    #
# (or higher) for the duration of the request.       #
#                                                    #
# Log levels: debug, info, notice, warning, error    #
#             critical, alert, emergency             #
######################################################

$LOGS_CONFIG['log_level']           = 'info';
$LOGS_CONFIG['log_level_threshold'] = 'error';

######################################################
# OPTIONAL : Page Logs                               #
######################################################
#                                                    #
# Sometimes a support agent may ask you to enable    #
# these options to help debug a problem              #
######################################################

$LOGS_CONFIG['page_log'] = array(
    /**
     * Enable the page log system
     */
    'enabled' => false,

    /**
     * Only enable for URLs that match any of these regex patterns.
     * For example: ['/agent\/tickets/']
     */
    'url_pattern' => [],

    /**
     * Slow Query Log: data/logs/pagelog-slow-queries.log
     * This logs queries that take longer than a certain time.
     *
     * Value: A a time in seconds
     * Example: 0.08 to log any query that takes longer than 0.08 secs
     */
    'slow_query_time' => false,

    /**
     * Query Count Log: data/logs/pagelog-query-count.log
     * This logs requests that execute more than a certain number of queries.
     *
     * Value: Number of queries to start logging on
     * Example: 10 to log any page that executes more than 10 queries
     */
    'max_query_count' => false,

    /**
     * Slow DB Log: data/logs/pagelog-slow-db.log
     * This logs pages where the total time spent doing database queries is over a certain time.
     *
     * Value: A time in seconds
     * Example: 0.5 to log any page where DB-work takes longer than 0.5 seconds.
     */
    'slow_db_time' => false,

    /**
     * Slow DB Log: data/logs/pagelog-slow-php.log
     * This logs pages where the total time spent in PHP is over a certain time.
     *
     * Value: A time in seconds
     * Example: 0.5 to log any page where PHP-work takes longer than 0.5 seconds.
     */
    'slow_php_time' => false,

    /**
     * Slow Page Log: data/logs/pagelog-slow-page.log
     * This logs any page that takes longer than a certain time to finish.
     *
     * Value: A time in seconds
     * Example: 0.8 to log any page that takes longer than 0.8 seconds from start to finish
     */
    'slow_page_time' => false,

    /**
     * Tracked Query Log: data/logs/pagelog-tracked-queries.log
     * This logs tracked queries (configured below).
     *
     * Value: true or false
     */
    'tracked_query_log' => false,

    /**
     * In the generated logs, queries are given an 'id'. If you add that ID to this array,
     * then a backtrace will be saved each time the query is executed. Use this to find
     * out where a certain query is being called from.
     *
     * Value: An array of strings which are query IDs.
     */
    'track_query_ids' => [],

    /**
     * Add regex patterns to this array and a backtrace will be saved each time the query
     * is executed. Similar to above using IDs except this uses regex instead.
     *
     * Value: An array of regular expressions to match against queries
     * Example: ['/UPDATE\s+permissions\s/i']
     */
    'track_query_regex' => [],
);
