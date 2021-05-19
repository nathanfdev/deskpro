<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TicketDepartmentValidator extends ConstraintValidator
{
    const UNAUTHORIZED_DEPARTMENT = 'unauthorized_department';

    /**
     * @var DepartmentDataService
     */
    private $departmentDataService;

    /**
     * @param DepartmentDataService $departmentDataService
     */
    public function __construct(
        DepartmentDataService $departmentDataService
    ) {
        $this->departmentDataService = $departmentDataService;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TicketDepartment) {
            throw new UnexpectedTypeException($constraint, TicketDepartment::class);
        }

        if (null === $value) {
            return;
        }

        $person = $value->getPerson();

        if (null === $person) {
            return;
        }

        $allowedTicketDepartments = new ArrayCollection($this->departmentDataService->getTicketDepartmentsForPerson($person, $value->getBrand()));

        if ($value->getDepartment() && !$allowedTicketDepartments->contains($value->getDepartment())) {
            /** @var ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(TicketDepartment::UNAUTHORIZED_DEPARTMENT)
                ->addViolation();
        }
    }
}
