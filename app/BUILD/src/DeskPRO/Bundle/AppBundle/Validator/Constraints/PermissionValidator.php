<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PermissionValidator.
 */
class PermissionValidator extends ConstraintValidator
{
    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * Constructor.
     *
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Permission) {
            throw new UnexpectedTypeException($constraint, Permission::class);
        }

        if (!$value) {
            return;
        }

        if (!is_array($value) && !$value instanceof \Traversable) {
            $value = [$value];
        }

        $failed = [];
        foreach ($value as $item) {
            /** @var EntityInterface $item */
            if (!$this->authorizationChecker->isGranted($constraint->action, new PermissionGroupContext($item))) {
                $failed[] = $item->getId();
            }
        }

        if (count($failed)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setParameter('value', implode(', ', $failed))
                ->setCode(Permission::NO_PERMISSION)
                ->addViolation()
            ;
        }
    }
}
