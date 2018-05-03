<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Application\DeskPRO\People\PasswordPolicyValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DpPasswordValidator extends ConstraintValidator
{
    /**
     * @var PasswordPolicyValidator
     */
    private $pw_validator;

    /**
     * Constructor.
     *
     * @param PasswordPolicyValidator $pw_validator
     */
    public function __construct(PasswordPolicyValidator $pw_validator)
    {
        $this->pw_validator = $pw_validator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DpPassword) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\DpPassword');
        }

        $person = $constraint->person;
        if (!$this->pw_validator->checkPassword($value, $person, $error)) {
            $policy = $this->pw_validator->getPolicy($person);

            $error_phrase = 'portal.forms.error_password_'.$error;
            $error_params = [];

            switch ($error) {
                case 'min_length':
                    $error_params['count'] = $policy->min_length;
                    break;
                case 'require_num_uppercase':
                    $error_params['count'] = $policy->require_num_uppercase;
                    break;
                case 'require_num_lowercase':
                    $error_params['count'] = $policy->require_num_lowercase;
                    break;
                case 'require_num_number':
                    $error_params['count'] = $policy->require_num_number;
                    break;
                case 'require_num_symbol':
                    $error_params['count'] = $policy->require_num_symbol;
                    break;
                case 'forbid_reuse':
                    $error_params['count'] = $policy->forbid_reuse;
                    break;
            }

            $this->buildViolation($error_phrase)
                    ->setParameters($error_params)
                    ->addViolation();
        }
    }
}
