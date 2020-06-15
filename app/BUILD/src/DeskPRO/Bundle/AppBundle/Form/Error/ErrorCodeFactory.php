<?php



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
    private $exceptionErrorCodeFactory;

    /**
     * @var ValidatorErrorCodeFactory
     */
    private $validatorErrorCodeFactory;

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
        $this->exceptionErrorCodeFactory = $exception_error_code_factory;
        $this->validatorErrorCodeFactory = $validator_error_code_factory;
    }

    /**
     * @param \Exception $e
     *
     * @return string|void
     */
    public function getErrorCodeForException(\Exception $e)
    {
        return $this->exceptionErrorCodeFactory->getExceptionErrorCode($e);
    }

    /**
     * @param ConstraintViolation $violation
     *
     * @return mixed|string
     */
    public function getErrorCodeForConstraintViolation(ConstraintViolation $violation)
    {
        return $this->validatorErrorCodeFactory->getConstraintErrorCode($violation);
    }

    /**
     * @param FormError $form_error
     *
     * @return mixed|string
     */
    public function getErrorCodeForFormError(FormError $form_error)
    {
        return $this->validatorErrorCodeFactory->getFormErrorCode($form_error);
    }
}
