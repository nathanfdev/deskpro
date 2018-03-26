<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Symfony\Component\Validator\Constraint;

/**
 * Class LeafChoice.
 */
class LeafChoice extends Constraint
{
    const NOT_ASSIGNABLE_CHOICE = 'not_assignable_choice';

    public $message = 'Unable to select parent choice.';

    /**
     * @var CustomDefAbstract
     */
    public $customDef;
}
