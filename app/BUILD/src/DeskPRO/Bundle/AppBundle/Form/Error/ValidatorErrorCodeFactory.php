<?php

namespace DeskPRO\Bundle\AppBundle\Form\Error;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Validator\Constraints\Form;
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
    private static $staticReplacements = [
        'This form should not contain extra fields.' => ErrorsCodes::EXTRA_FIELDS,
    ];

    /**
     * Mapping of TransformationFailedException messages to own error codes.
     *
     * @var array
     */
    private static $transformationFailedMapping = [
        '/Number parsing failed/'                                    => ErrorsCodes::NUMERIC,
        '/The choice ".*" does not exist or is not unique/'          => ErrorsCodes::BAD_CHOICE,
        '/The choices ".*" do not exist in the choice list./'        => ErrorsCodes::BAD_CHOICE,
        '/Could not find all matching choices for the given values/' => ErrorsCodes::BAD_CHOICE,
        '/All values in the array should be the same/'               => ErrorsCodes::MISMATCH_VALUES,
    ];

    /**
     * Mapping of build-in validators to own error codes.
     *
     * Sf validator error codes are hashes like '60d2f30b-8cfa-4372-b155-9656634de120'
     * so we map them to human readable values.
     *
     * @var array
     */
    private static $errorCodeMapping = [
        Form::NO_SUCH_FIELD_ERROR                => ErrorsCodes::EXTRA_FIELDS,
        Assert\IsNull::NOT_NULL_ERROR            => ErrorsCodes::NULL,
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
        Assert\Range::TOO_LOW_ERROR              => ErrorsCodes::TOO_LOW,
        Assert\LessThanOrEqual::TOO_HIGH_ERROR   => ErrorsCodes::TOO_HIGH,
        Assert\Range::TOO_HIGH_ERROR             => ErrorsCodes::TOO_HIGH,
        Assert\IsTrue::NOT_TRUE_ERROR            => ErrorsCodes::NOT_CHECKED,
        Assert\Regex::REGEX_FAILED_ERROR         => ErrorsCodes::REGEX,
        Assert\Image::INVALID_MIME_TYPE_ERROR    => ErrorsCodes::NOT_AN_IMAGE,
        Assert\File::NOT_FOUND_ERROR             => ErrorsCodes::NO_UPLOADED_FILE,
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

        // handle TransformationFailedExceptions
        if ($violation->getCause() instanceof TransformationFailedException) {
            foreach (self::$transformationFailedMapping as $pattern => $errorCode) {
                if (preg_match($pattern, $violation->getCause()->getMessage())) {
                    return $errorCode;
                }
            }

            return ErrorsCodes::INVALID_DATA_TYPE;
        }

        // handle constraints
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

        // use violation message as fallback
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
