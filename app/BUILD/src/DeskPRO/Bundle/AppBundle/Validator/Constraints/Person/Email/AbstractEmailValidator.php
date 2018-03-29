<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class AbstractEmailValidator.
 */
abstract class AbstractEmailValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AbstractEmail) {
            throw new UnexpectedTypeException($constraint, AbstractEmail::class);
        }

        if (!$value) {
            return;
        }

        if ($value instanceof Person) {
            $this->validatePerson($value, $constraint);
        } elseif ($value instanceof PersonEmail) {
            $this->validatePersonEmail($value, $constraint);
        } else {
            throw new UnexpectedTypeException($value, implode(', ', [Person::class, PersonEmail::class]));
        }
    }

    /**
     * @param Person        $value
     * @param AbstractEmail $constraint
     */
    protected function validatePerson(Person $value, AbstractEmail $constraint)
    {
        foreach ($value->getEmails() as $personEmail) {
            if ($personEmail) {
                $this->validatePersonEmail($personEmail, $constraint);
            }
        }
        if ($value->getPrimaryEmail() && !$value->getEmails()->contains($value->getPrimaryEmail())) {
            $this->validatePersonEmail($value->getPrimaryEmail(), $constraint);
        }
    }

    /**
     * @param PersonEmail   $value
     * @param AbstractEmail $constraint
     */
    protected function validatePersonEmail(PersonEmail $value, AbstractEmail $constraint)
    {
        if (!$this->isValidEmail($value)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setParameter('email', $value->getEmail())
                ->setCode($constraint->getErrorCode())
                ->atPath($constraint->property)
                ->addViolation()
            ;
        }
    }

    /**
     * @param PersonEmail $value
     *
     * @return bool
     */
    abstract protected function isValidEmail(PersonEmail $value);
}
