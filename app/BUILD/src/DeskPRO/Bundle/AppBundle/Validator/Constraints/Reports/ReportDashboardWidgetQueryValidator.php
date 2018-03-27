<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Reports;

use Application\DeskPRO\Entity\ReportDashboardWidget;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ReportDashboardWidgetValidator.
 */
class ReportDashboardWidgetQueryValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ReportDashboardWidgetQuery) {
            throw new UnexpectedTypeException($constraint, ReportDashboardWidgetQuery::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof ReportDashboardWidget) {
            throw new UnexpectedTypeException($value, ReportDashboardWidget::class);
        }

        if (!$value->getWidget() && !$value->getJsCode()) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(ReportDashboardWidgetQuery::NO_QUERY_PROVIDED)
                ->addViolation()
            ;
        }
    }
}
