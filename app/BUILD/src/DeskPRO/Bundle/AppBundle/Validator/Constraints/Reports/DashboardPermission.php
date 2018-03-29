<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Reports;

use Symfony\Component\Validator\Constraint;

/**
 * Class DashboardPermission.
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class DashboardPermission extends Constraint
{
    const NO_DEFAULT_DASHBOARD_PERMISSION = 'no_default_dashboard_permission';

    public $message = 'can\'t grant edit permission for agent to default dashboard';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT];
    }
}
