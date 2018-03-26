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

    public $message = 'Unable to parse this DPQL query.';
}
