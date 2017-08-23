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
