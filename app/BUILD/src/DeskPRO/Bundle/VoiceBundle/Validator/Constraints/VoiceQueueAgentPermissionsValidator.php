<?php

namespace DeskPRO\Bundle\VoiceBundle\Validator\Constraints;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\VoiceBundle\Permissions\VoicePermissionsChecker;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class VoiceQueueAgentPermissionsValidator.
 */
class VoiceQueueAgentPermissionsValidator extends ConstraintValidator
{
    /**
     * @var VoicePermissionsChecker
     */
    private $departmentChecker;

    /**
     * Constructor.
     *
     * @param VoicePermissionsChecker $departmentChecker
     */
    public function __construct(VoicePermissionsChecker $departmentChecker)
    {
        $this->departmentChecker = $departmentChecker;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedTypeException
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
            $agent = $queueAgent->getAgent();

            if (!$this->departmentChecker->canBeMemberOfVoiceQueue($value, $agent)) {
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
