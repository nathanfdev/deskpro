<?php

/**
 * DeskPRO.
 *
 * @category Validator
 */

namespace Application\DeskPRO\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class AgentGroupConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Invalid agent group';

    /**
     * @var string
     */
    public $typeMessage = 'Invalid object type, got {{type}}';

    /**
     * Accepts and validates IDs (as well as actual objects).
     *
     * @var bool
     */
    public $acceptId = true;

    /**
     * If given a Usergroup, checks that its actually in the repository.
     *
     * @var bool
     */
    public $checkRepos = true;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'AgentGroup';
    }
}
