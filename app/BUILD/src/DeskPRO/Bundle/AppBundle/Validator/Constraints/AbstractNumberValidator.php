<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Orb\Util\PhoneNumbers;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class AbstractCallNumberValidator.
 */
abstract class AbstractNumberValidator extends ConstraintValidator
{
    /**
     * @param string         $value
     * @param AbstractNumber $constraint
     */
    protected function validatePhoneNumber($value, AbstractNumber $constraint)
    {
        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;

        if (PhoneNumbers::looksEmpty($value)) {
            $context
                ->buildViolation($constraint->invalidFormatMessage)
                ->setCode(PhoneNumber::INVALID_PHONE_NUMBER)
                ->addViolation()
            ;

            return;
        }

        try {
            $number = PhoneNumberUtil::getInstance()->parse($value, null);
            if (!PhoneNumberUtil::getInstance()->isValidNumber($number)) {
                $context
                    ->buildViolation($constraint->invalidFormatMessage)
                    ->setCode(PhoneNumber::INVALID_PHONE_NUMBER)
                    ->addViolation()
                ;
            }
        } catch (NumberParseException $e) {
            switch ($e->getErrorType()) {
                case NumberParseException::INVALID_COUNTRY_CODE:
                    $context
                        ->buildViolation($constraint->missingCountryCodeMessage)
                        ->setCode(PhoneNumber::MISSING_COUNTRY_CODE)
                        ->addViolation()
                    ;
                    break;
                case NumberParseException::NOT_A_NUMBER:
                case NumberParseException::TOO_SHORT_AFTER_IDD:
                case NumberParseException::TOO_SHORT_NSN:
                case NumberParseException::TOO_LONG:
                default:
                    $context
                        ->buildViolation($constraint->invalidFormatMessage)
                        ->setCode(PhoneNumber::INVALID_PHONE_NUMBER)
                        ->addViolation()
                    ;
                    break;
            }
        } catch (\Exception $e) {
            $context
                ->buildViolation($constraint->invalidFormatMessage)
                ->setCode(PhoneNumber::INVALID_PHONE_NUMBER)
                ->addViolation()
            ;
        }
    }
}
