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

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
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
    public static $static_replacements = [
        'This form should not contain extra fields.' => ApiErrors::EXTRA_FIELDS,
    ];

    /**
     * @param ConstraintViolation $violation
     *
     * @return mixed|string
     */
    public function getConstraintErrorCode(ConstraintViolation $violation)
    {
        if ($violation->getMessage() === 'This form should not contain extra fields.') {
            return ApiErrors::EXTRA_FIELDS;
        }
        if ($violation->getCause() instanceof TransformationFailedException) {
            if (preg_match('/The choice ".*" does not exist or is not unique/', $violation->getCause()->getMessage())) {
                return ApiErrors::BAD_CHOICE;
            }

            return ApiErrors::INVALID_DATA_TYPE;
        }

        $constraint = $violation->getConstraint();
        if ($constraint) {
            switch (get_class($constraint)) {
                case Assert\NotNull::class:
                    return ApiErrors::NOT_NULL;
                case Assert\NotBlank::class:
                    return ApiErrors::NOT_NULL;
                case Assert\Type::class:
                    return ApiErrors::INVALID_DATA_TYPE;
                case Assert\Length::class:
                    return $violation->getCode() === Assert\Length::TOO_SHORT_ERROR
                        ? ApiErrors::LENGTH_TOO_SHORT
                        : ApiErrors::LENGTH_TOO_LONG;
                case Assert\Valid::class:
                    return ApiErrors::INVALID_INPUT;
                case Assert\Choice::class:
                    return ApiErrors::BAD_CHOICE;
                case Assert\Email::class:
                    return ApiErrors::INVALID_EMAIL;
                case Assert\Count::class:
                    return $violation->getCode() === Assert\Count::TOO_FEW_ERROR
                        ? ApiErrors::TOO_FEW_ELEMENTS
                        : ApiErrors::TOO_MANY_ELEMENTS;
                case Assert\Url::class:
                    return ApiErrors::INVALID_URL;
                case AppAssert\ProfileUrl::class:
                    return ApiErrors::PROFILE_URL;
                case UniqueEntity::class:
                    return ApiErrors::UNIQUE_ENTITY;
                case AppAssert\ProjectMember::class:
                    return ApiErrors::EXACTLY_ONE_SHOULD_BE_SET;
            }
        }

        $code = $violation->getMessage();
        if ($code) {
            return $this->filterCode($code);
        }

        return ApiErrors::CONSTRAINT_FALLBACK;
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

        return ApiErrors::CONSTRAINT_FALLBACK;
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    private function filterCode($code)
    {
        if (array_key_exists($code, self::$static_replacements)) {
            $code = self::$static_replacements[$code];
        }

        return $code;
    }
}
