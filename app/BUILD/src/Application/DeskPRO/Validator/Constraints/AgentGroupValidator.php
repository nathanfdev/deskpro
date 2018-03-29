<?php

/**
 * DeskPRO.
 *
 * @category Validator
 */

namespace Application\DeskPRO\Validator\Constraints;

use Application\DeskPRO\DependencyInjection\SystemServices\UsergroupDataService;
use Application\DeskPRO\Entity\Usergroup;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AgentGroupValidator extends ConstraintValidator
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\SystemServices\UsergroupDataService
     */
    private $usergroup_data;

    /**
     * @param UsergroupDataService $usergroup_data
     */
    public function __construct(UsergroupDataService $usergroup_data)
    {
        $this->usergroup_data = $usergroup_data;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (is_object($value)) {
            if (!($value instanceof Usergroup)) {
                $this->context->addViolation($constraint->typeMessage, ['{{type}}' => get_class($value)]);
            } else {
                if ($constraint->checkRepos) {
                    if (!$value->id || !$this->usergroup_data->getAgentGroup($value->id)) {
                        $this->context->addViolation($constraint->message);
                    }
                }
            }
        } else {
            if (!$constraint->acceptId) {
                $this->context->addViolation($constraint->message);
            }

            if (!$this->usergroup_data->getAgentGroup($value)) {
                $this->context->addViolation($constraint->message);
            }
        }
    }

    /**
     * @return string
     */
    public static function getAlias()
    {
        return 'AgentGroup';
    }
}
