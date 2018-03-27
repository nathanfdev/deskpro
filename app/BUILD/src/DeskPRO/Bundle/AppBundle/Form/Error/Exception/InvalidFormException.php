<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Error\Exception;

use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Throw this when a form fails validation. Throwing this will trigger our ExceptionController to
 * render the proper response.
 */
class InvalidFormException extends BadRequestHttpException implements FormExceptionInterface
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * {@inheritdoc}
     */
    public function __construct(FormInterface $form, \Exception $previous = null, $code = 0)
    {
        $this->form = $form;

        parent::__construct(ErrorsCodes::INVALID_INPUT, $previous, $code);
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
