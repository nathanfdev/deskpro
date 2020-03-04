<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Settings;

use Symfony\Component\Validator\Constraint;

/**
 * Class KbReviewDate.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class KbReviewDate extends Constraint
{
    const DEFAULT_REVIEW_DATE_LESS_THAN_MIN_REVIEW_DATE    = 'default_review_date_less_than_min_review_date';
    const DEFAULT_REVIEW_DATE_GREATER_THAN_MAX_REVIEW_DATE = 'default_review_date_greater_than_max_review_date';
    const MIN_REVIEW_DATE_GREATER_THAN_MAX_REVIEW_DATE     = 'min_review_date_greater_than_max_review_date';

    public $defaultReviewDateLessThanMinReviewDateMessage    = 'Default review date interval should be greater than or equal to min review date interval.';
    public $defaultReviewDateGreaterThanMaxReviewDateMessage = 'Default review date interval should be less than or equal to the max review date interval.';
    public $minReviewDateGreaterThanMaxReviewDateMessage     = 'Max review date interval should greater than or equal to the min review date interval.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
