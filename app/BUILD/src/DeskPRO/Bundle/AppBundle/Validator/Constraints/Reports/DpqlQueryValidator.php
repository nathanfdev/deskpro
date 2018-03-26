<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Reports;

use DeskPRO\Bundle\ReportBundle\Dashboard\DashboardWidgetManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class DpqlQueryValidator.
 */
class DpqlQueryValidator extends ConstraintValidator
{
    /**
     * @var DashboardWidgetManager
     */
    private $dashboardWidget;

    /**
     * Constructor.
     *
     * @param DashboardWidgetManager $dashboardWidget
     */
    public function __construct(DashboardWidgetManager $dashboardWidget)
    {
        $this->dashboardWidget = $dashboardWidget;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DpqlQuery) {
            throw new UnexpectedTypeException($constraint, DpqlQuery::class);
        }
        if (!$value) {
            return;
        }

        try {
            $this->dashboardWidget->getCompiledQueries($value);
        } catch (\Exception $e) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(DpqlQuery::INVALID_DPQL_QUERY)
                ->addViolation()
            ;
        }
    }
}
