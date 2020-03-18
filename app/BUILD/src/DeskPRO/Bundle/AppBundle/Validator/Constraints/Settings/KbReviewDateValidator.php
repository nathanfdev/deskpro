<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Settings;

use Application\DeskPRO\Entity\Article;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\KbSettings;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class KbReviewDateValidator.
 */
class KbReviewDateValidator extends ConstraintValidator
{
    /**
     * @var \DateTime
     */
    private $now;

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof KbReviewDate) {
            throw new UnexpectedTypeException($constraint, KbReviewDate::class);
        }

        if (!$value instanceof KbSettings) {
            throw new UnexpectedTypeException($value, KbSettings::class);
        }

        $minDate     = null;
        $maxDate     = null;
        $defaultDate = null;

        if ($value->isMinReviewDate()) {
            $minDate = $this->getDateTimeFromInterval($value->getMinReviewDateInterval(), $value->getMinReviewDateUnit());
        }
        if ($value->isMaxReviewDate()) {
            $maxDate = $this->getDateTimeFromInterval($value->getMaxReviewDateInterval(), $value->getMaxReviewDateUnit());
        }
        if ($value->isDefaultReviewDate()) {
            $defaultDate = $this->getDateTimeFromInterval($value->getDefaultReviewDateInterval(), $value->getDefaultReviewDateUnit());
        }

        if ($value->isMinReviewDate() && $value->isDefaultReviewDate() && $minDate > $defaultDate) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->defaultReviewDateLessThanMinReviewDateMessage)
                ->setCode(KbReviewDate::DEFAULT_REVIEW_DATE_LESS_THAN_MIN_REVIEW_DATE)
                ->atPath('defaultReviewDateInterval')
                ->setParameter('min_interval', $value->getMinReviewDateInterval())
                ->setParameter('min_unit', $value->getMinReviewDateUnit())
                ->setParameter('default_interval', $value->getDefaultReviewDateInterval())
                ->setParameter('default_unit', $value->getDefaultReviewDateUnit())
                ->addViolation()
            ;
        }

        if ($value->isMaxReviewDate() && $value->isDefaultReviewDate() && $defaultDate > $maxDate) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->defaultReviewDateGreaterThanMaxReviewDateMessage)
                ->setCode(KbReviewDate::DEFAULT_REVIEW_DATE_GREATER_THAN_MAX_REVIEW_DATE)
                ->atPath('defaultReviewDateInterval')
                ->setParameter('max_interval', $value->getMaxReviewDateInterval())
                ->setParameter('max_unit', $value->getMaxReviewDateUnit())
                ->setParameter('default_interval', $value->getDefaultReviewDateInterval())
                ->setParameter('default_unit', $value->getDefaultReviewDateUnit())
                ->addViolation()
            ;
        }

        if ($value->isMinReviewDate() && $value->isMaxReviewDate() && $minDate > $maxDate) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->minReviewDateGreaterThanMaxReviewDateMessage)
                ->setCode(KbReviewDate::MIN_REVIEW_DATE_GREATER_THAN_MAX_REVIEW_DATE)
                ->atPath('defaultReviewDateInterval')
                ->setParameter('min_interval', $value->getMinReviewDateInterval())
                ->setParameter('min_unit', $value->getMinReviewDateUnit())
                ->setParameter('max_interval', $value->getMaxReviewDateInterval())
                ->setParameter('max_unit', $value->getMaxReviewDateUnit())
                ->addViolation()
            ;
        }
    }

    /**
     * @param int    $interval
     * @param string $unit
     *
     * @return \DateTime
     */
    private function getDateTimeFromInterval($interval, $unit)
    {
        if (!$unit) {
            $unit = Article::REVIEW_DATE_UNIT_DAYS;
        }
        if (!$this->now) {
            $this->now = new \DateTime();
        }

        $date = clone $this->now;

        if ($interval < 0) {
            $interval = 0;
        }

        return $date->modify("+$interval $unit");
    }
}
