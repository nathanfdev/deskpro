<?php

namespace DeskPRO\Bundle\AppBundle\Form\Error\Exception;

use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AbuseCaptchaFormException.
 */
class AbuseCaptchaFormException extends BadRequestHttpException implements FormExceptionInterface
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

        parent::__construct(ErrorsCodes::CAPTCHA_REQUIRED, $previous, $code);
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
