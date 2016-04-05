<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Error;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class ValidatorErrorCodeFactory.
 */
class ValidatorErrorCodeFactory
{
    /**
     * @var array
     */
    public static $staticReplacements = [
        'This form should not contain extra fields.' => ErrorsCodes::EXTRA_FIELDS,
    ];

    /**
     * @var array
     */
    private static $errorCodeMapping = [
        Assert\NotNull::IS_NULL_ERROR            => ErrorsCodes::NOT_NULL,
        Assert\NotBlank::IS_BLANK_ERROR          => ErrorsCodes::NOT_NULL,
        Assert\Type::INVALID_TYPE_ERROR          => ErrorsCodes::INVALID_DATA_TYPE,
        Assert\Length::TOO_SHORT_ERROR           => ErrorsCodes::LENGTH_TOO_SHORT,
        Assert\Length::TOO_LONG_ERROR            => ErrorsCodes::LENGTH_TOO_LONG,
        Assert\Choice::NO_SUCH_CHOICE_ERROR      => ErrorsCodes::BAD_CHOICE,
        Assert\Count::TOO_FEW_ERROR              => ErrorsCodes::TOO_FEW_ELEMENTS,
        Assert\Count::TOO_MANY_ERROR             => ErrorsCodes::TOO_MANY_ELEMENTS,
        Assert\Url::INVALID_URL_ERROR            => ErrorsCodes::INVALID_URL,
        Assert\GreaterThanOrEqual::TOO_LOW_ERROR => ErrorsCodes::TOO_LOW,
    ];

    /**
     * @param ConstraintViolation $violation
     *
     * @return mixed|string
     */
    public function getConstraintErrorCode(ConstraintViolation $violation)
    {
        if ($violation->getMessage() === 'This form should not contain extra fields.') {
            return ErrorsCodes::EXTRA_FIELDS;
        }
        if ($violation->getCause() instanceof TransformationFailedException) {
            if (preg_match('/The choice ".*" does not exist or is not unique/', $violation->getCause()->getMessage())) {
                return ErrorsCodes::BAD_CHOICE;
            }

            return ErrorsCodes::INVALID_DATA_TYPE;
        }

        $constraint = $violation->getConstraint();
        if ($constraint) {
            switch (get_class($constraint)) {
                case Assert\Valid::class:
                    return ErrorsCodes::INVALID_INPUT;
                case Assert\Email::class:
                    return ErrorsCodes::INVALID_EMAIL;
                case UniqueEntity::class:
                    return ErrorsCodes::UNIQUE_ENTITY;
            }
        }

        $code = $violation->getCode() ?: $violation->getMessage();
        if ($code) {
            return $this->filterCode($code);
        }

        return ErrorsCodes::CONSTRAINT_FALLBACK;
    }

    /**
     * @param FormError $form_error
     *
     * @return mixed|string
     */
    public function getFormErrorCode(FormError $form_error)
    {
        $cause = $form_error->getCause();
        if ($cause instanceof ConstraintViolation) {
            return $this->getConstraintErrorCode($cause);
        }

        $code = $form_error->getMessage();
        if ($code) {
            return $this->filterCode($code);
        }

        return ErrorsCodes::CONSTRAINT_FALLBACK;
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    private function filterCode($code)
    {
        if (array_key_exists($code, self::$staticReplacements)) {
            $code = self::$staticReplacements[$code];
        }
        if (array_key_exists($code, self::$errorCodeMapping)) {
            $code = self::$errorCodeMapping[$code];
        }

        return $code;
    }
}
