<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Application\DeskPRO\Entity\Department;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class LeafDepartmentValidator.
 */
class LeafDepartmentValidator extends ConstraintValidator
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * LeafDepartmentValidator constructor.
     *
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($department, Constraint $constraint)
    {
        if (is_null($department)) {
            return;
        }
        if (!$department instanceof Department) {
            throw new UnexpectedTypeException($department, Department::class);
        }
        if (!$constraint instanceof LeafDepartment) {
            throw new UnexpectedTypeException($constraint, LeafDepartment::class);
        }

        if ($this->repository->findOneBy(['parent' => $department])) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(LeafDepartment::NOT_ASSIGNABLE_DEPARTMENT)
                ->addViolation()
            ;
        }
    }
}
