<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\People\PersonEmail;

use Application\DeskPRO\Entity\PersonEmail;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class PersonEmailConstraintValidator.
 */
class PersonEmailConstraintValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($personEmail, Constraint $constraint)
    {
        if (!$constraint instanceof PersonEmailConstraint) {
            throw new \Exception('Expected PersonPersonEmailConstraint instance, got '.$constraint);
        }

        if (!$personEmail) {
            return;
        }

        if (!$personEmail instanceof PersonEmail) {
            $this->context->addViolation('Expected PersonEmail instance, got '.$personEmail);
        }

        // Comparing to prev person as during form binding and $person->setEmail($email) call $email->setPerson($this)
        // could be called
        $emailPersonId                   = $personEmail->getPrevPersonId();
        $emailPersonId or $emailPersonId = $personEmail->getPersonId();
        $person                          = $constraint->getPerson();
        if ($person->getId() !== $emailPersonId) {
            $this->context->addViolation('This email is already in use');
        }
    }
}
