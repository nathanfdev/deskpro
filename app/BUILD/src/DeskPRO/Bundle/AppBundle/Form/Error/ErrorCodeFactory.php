<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Error;

use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class ErrorCodeFactory.
 */
class ErrorCodeFactory
{
    /**
     * @var ExceptionErrorCodeFactory
     */
    private $exception_error_code_factory;

    /**
     * @var ValidatorErrorCodeFactory
     */
    private $validator_error_code_factory;

    /**
     * Constructor.
     *
     * @param ExceptionErrorCodeFactory $exception_error_code_factory
     * @param ValidatorErrorCodeFactory $validator_error_code_factory
     */
    public function __construct(
        ExceptionErrorCodeFactory $exception_error_code_factory,
        ValidatorErrorCodeFactory $validator_error_code_factory
    ) {
        $this->exception_error_code_factory = $exception_error_code_factory;
        $this->validator_error_code_factory = $validator_error_code_factory;
    }

    /**
     * @param \Exception $e
     *
     * @return string|void
     */
    public function getErrorCodeForException(\Exception $e)
    {
        return $this->exception_error_code_factory->getExceptionErrorCode($e);
    }

    /**
     * @param ConstraintViolation $violation
     *
     * @return mixed|string
     */
    public function getErrorCodeForConstraintViolation(ConstraintViolation $violation)
    {
        return $this->validator_error_code_factory->getConstraintErrorCode($violation);
    }

    /**
     * @param FormError $form_error
     *
     * @return mixed|string
     */
    public function getErrorCodeForFormError(FormError $form_error)
    {
        return $this->validator_error_code_factory->getFormErrorCode($form_error);
    }
}
