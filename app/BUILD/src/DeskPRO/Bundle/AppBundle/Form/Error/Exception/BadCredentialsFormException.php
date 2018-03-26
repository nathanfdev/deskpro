<?php

namespace DeskPRO\Bundle\AppBundle\Form\Error\Exception;

use DeskPRO\Bundle\ApiBundle\Security\Authentication\ApiAuthenticator;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Throw this when a form fails validation. Throwing this will trigger our ExceptionController to
 * render the proper response.
 */
class BadCredentialsFormException extends UnauthorizedHttpException implements FormExceptionInterface
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

        parent::__construct(ApiAuthenticator::HTTP_REALM, ErrorsCodes::BAD_CREDENTIALS, $previous, $code);
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
