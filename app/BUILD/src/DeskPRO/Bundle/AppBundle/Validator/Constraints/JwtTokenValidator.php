<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class JwtTokenValidator.
 */
class JwtTokenValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof JwtToken) {
            throw new UnexpectedTypeException($constraint, JwtToken::class);
        }

        if (!$value) {
            if ($constraint->required) {
                /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
                $context = $this->context;
                $context
                    ->buildViolation($constraint->missingMessage)
                    ->setCode(JwtToken::MISSING_JWT_TOKEN)
                    ->addViolation()
                ;
            }

            return;
        }

        try {
            JWT::decode(
                $value,
                $constraint->secret,
                $constraint->algo ? [$constraint->algo] : array_keys(JWT::$supported_algs)
            );
        } catch (ExpiredException $e) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->expiredMessage)
                ->setCode(JwtToken::EXPIRED_JWT_TOKEN)
                ->addViolation()
            ;
        } catch (\Exception $e) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->invalidMessage)
                ->setCode(JwtToken::INVALID_JWT_TOKEN)
                ->addViolation()
            ;
        }
    }
}
