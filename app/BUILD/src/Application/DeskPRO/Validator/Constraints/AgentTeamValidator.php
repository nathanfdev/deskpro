<?php

/**
 * DeskPRO.
 *
 * @category Validator
 */

namespace Application\DeskPRO\Validator\Constraints;

use Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService;
use Application\DeskPRO\Entity\AgentTeam;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AgentTeamValidator extends ConstraintValidator
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService
     */
    private $agent_data;

    /**
     * @param AgentDataService $agent_data
     */
    public function __construct(AgentDataService $agent_data)
    {
        $this->agent_data = $agent_data;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (is_object($value)) {
            if (!($value instanceof AgentTeam)) {
                $this->context->addViolation($constraint->typeMessage, ['{{type}}' => get_class($value)]);
            } else {
                if ($constraint->checkRepos) {
                    if (!$value->id || !$this->agent_data->getTeam($value->id)) {
                        $this->context->addViolation($constraint->message);
                    }
                }
            }
        } else {
            if (!$constraint->acceptId) {
                $this->context->addViolation($constraint->message);
            }

            if (!$this->agent_data->getAgentGroup($value)) {
                $this->context->addViolation($constraint->message);
            }
        }
    }

    /**
     * @return string
     */
    public static function getAlias()
    {
        return 'AgentTeam';
    }
}
