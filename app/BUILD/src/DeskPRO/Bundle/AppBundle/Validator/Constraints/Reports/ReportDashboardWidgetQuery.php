<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Reports;

use Symfony\Component\Validator\Constraint;

/**
 * Class ReportDashboardWidget.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class ReportDashboardWidgetQuery extends Constraint
{
    const NO_QUERY_PROVIDED = 'no_source_provided';

    public $message = 'Unable to fetch data, no report widget or js code provided.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
