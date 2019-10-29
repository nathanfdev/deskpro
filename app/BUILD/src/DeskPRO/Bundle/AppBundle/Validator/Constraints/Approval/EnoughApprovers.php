<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use Symfony\Component\Validator\Constraint;

/**
 * Class EnoughApprovers.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION", "CLASS"})
 */
class EnoughApprovers extends Constraint
{
    const NOT_ENOUGH_APPROVERS = 'not_enough_approvers';

    public $message = 'There aren\'t enough approvers defined to meet required approvals/rejections';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
