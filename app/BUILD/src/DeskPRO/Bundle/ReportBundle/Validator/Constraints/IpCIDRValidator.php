<?php

namespace DeskPRO\Bundle\ReportBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\IpValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class IpCIDRValidator
 *
 * @package DeskPRO\Bundle\ReportBundle\Validator\Constraints
 */
class IpCIDRValidator extends IpValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IpCIDR) {
            throw new UnexpectedTypeException($constraint, IpCIDR::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;

        list($ip, $netmask) = explode('/', $value);

        if (empty($netmask)) {
            parent::validate($value, $constraint);
        } else {
            $isValid = false;

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $isValid = $netmask >= 0 && $netmask <= 32;
            } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $isValid = $netmask >= 0 && $netmask <= 128;
            }

            if (!$isValid) {
                if ($this->context instanceof ExecutionContextInterface) {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('{{ value }}', $this->formatValue($value))
                        ->setCode(IpCIDR::INVALID_IP_ERROR)
                        ->addViolation();
                } else {
                    $this->buildViolation($constraint->message)
                        ->setParameter('{{ value }}', $this->formatValue($value))
                        ->setCode(IpCIDR::INVALID_IP_ERROR)
                        ->addViolation();
                }
            }
        }
    }
}
