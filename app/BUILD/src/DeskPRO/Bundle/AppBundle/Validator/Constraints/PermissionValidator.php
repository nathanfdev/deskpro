<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
