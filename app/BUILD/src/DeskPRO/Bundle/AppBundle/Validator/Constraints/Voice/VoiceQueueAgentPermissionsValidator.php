<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class VoiceQueueAgentPermissionsValidator.
 */
class VoiceQueueAgentPermissionsValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof VoiceQueueAgentPermissions) {
            throw new UnexpectedTypeException($constraint, VoiceQueueAgentPermissions::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof VoiceQueue) {
            throw new UnexpectedTypeException($value, VoiceQueue::class);
        }

        $department = $value->getDepartment();
        if (!$department) {
            return;
        }

        foreach ($value->getAgents() as $queueAgent) {
            $agent                      = $queueAgent->getAgent();
            $permissionsHelper          = $agent->getHelper('AgentPermissions');
            $allowedTicketDepartmentIds = $permissionsHelper->getAllowedDepartments('tickets', false, 'full');

            if (!in_array($department->getId(), $allowedTicketDepartmentIds)) {
                /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
                $context = $this->context;
                $context
                    ->buildViolation($constraint->message, [
                        'agent'      => $agent->getName(),
                        'department' => $department->getTitle(),
                    ])
                    ->setCode(VoiceQueueAgentPermissions::NO_DEPARTMENT_PERMISSION)
                    ->addViolation()
                ;
            }
        }
    }
}
