<?php

namespace DeskPRO\Bundle\AppBundle\Validator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class ValidatorErrorsException.
 */
class ValidatorErrorsException extends BadRequestHttpException
{
    /**
     * @var ConstraintViolationListInterface
     */
    private $errors;

    /**
     * Constructor.
     *
     * @param ConstraintViolationListInterface $errors
     * @param string                           $message
     * @param int                              $code
     */
    public function __construct(ConstraintViolationListInterface $errors, $message = '', $code = 0)
    {
        parent::__construct($message, null, $code);

        $this->errors = $errors;
    }

    /**
     * @return ConstraintViolationListInterface|ConstraintViolationInterface[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
