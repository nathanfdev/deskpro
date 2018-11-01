<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use DeskPRO\Bundle\ApiBundle\Controller\ExceptionController as BaseExceptionController;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\FormExceptionInterface;
use DeskPRO\Bundle\AppBundle\Validator\ValidatorErrorsException;
use DeskPRO\Bundle\MessengerBundle\Exception\MessengerApiException;

/**
 * Class ExceptionController.
 */
class ExceptionController extends BaseExceptionController
{
    /**
     * @param $exception
     *
     * @return array
     */
    protected function getErrorsArray($exception)
    {
        $errorsArray = [];
        if ($exception instanceof MessengerApiException) {
            $errorsArray = $exception->getErrors();
        } elseif ($exception instanceof FormExceptionInterface) {
            $errorsArray = $this->get('form_error.form_errors_generator.api')->generateFormErrors($exception->getForm());
        } elseif ($exception instanceof ValidatorErrorsException) {
            $errorsArray = $this->get('form_error.validator_errors_generator.api')->generateValidatorErrors($exception->getErrors());
        }

        return $errorsArray;
    }
}
