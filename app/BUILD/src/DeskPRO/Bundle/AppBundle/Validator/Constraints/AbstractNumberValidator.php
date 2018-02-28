<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
