<?php

namespace Application\DeskPRO\Form\Transformer;

use Egulias\EmailValidator\EmailValidator;
use Orb\Util\PhoneNumbers;
use Symfony\Component\Form\DataTransformerInterface;

class PhoneNumberModelTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($number)
    {
        return $number;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($number)
    {
        if (!$number) {
            return;
        }

        try {
            if (preg_match('/^sip:.+/', $number)) {
                $email = preg_replace('/^sip:/', '', $number);
                if (!$email) {
                    return;
                }

                $strictValidator = new EmailValidator();
                if (preg_match('/^.+\@\S+\.\S+$/', $email) && $strictValidator->isValid($email, false, true)) {
                    return $number;
                }
            }

            if (!$formatted = PhoneNumbers::toE164Format($number)) {
                return;
            }
            if (!$region = PhoneNumbers::getRegionForNumber($number)) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        return $formatted;
    }
}
