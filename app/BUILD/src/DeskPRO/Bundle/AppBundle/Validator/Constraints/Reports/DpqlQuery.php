<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Reports;

use Symfony\Component\Validator\Constraint;

/**
 * Class DpqlQuery.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class DpqlQuery extends Constraint
{
    const INVALID_DPQL_QUERY = 'invalid_dpql_query';
    const DPQL_SYNTAX_ERROR  = 'dpql_syntax_error';

    public $message       = 'Unable to parse this DPQL query.';
    public $messageSyntax = 'Error parsing DPQL statement at line {{line}} (got {{token}}).';
}
