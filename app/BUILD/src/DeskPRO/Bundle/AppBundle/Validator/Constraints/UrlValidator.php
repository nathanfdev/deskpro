<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class UrlValidator.
 */
class UrlValidator extends \Symfony\Component\Validator\Constraints\UrlValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Url) {
            throw new UnexpectedTypeException($constraint, Url::class);
        }
        if (!$value) {
            return;
        }
        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ($constraint->allowFile) {
            if (preg_match('#^\\\\[\w\d-_\\\]+$#', $value)) {
                // shared folder
                return;
            }

            $constraint->protocols = ['\w+'];
        }

        parent::validate($value, $constraint);
    }
}
