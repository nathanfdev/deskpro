<?php

namespace DeskPRO\Bundle\AppBundle\Form\Error\Exception;

use Symfony\Component\Form\FormInterface;

/**
 * Interface FormExceptionInterface.
 */
interface FormExceptionInterface
{
    /**
     * Constructor.
     *
     * @param FormInterface   $form
     * @param \Exception|null $previous
     * @param int             $code
     */
    public function __construct(FormInterface $form, \Exception $previous = null, $code = 0);

    /**
     * @return FormInterface
     */
    public function getForm();
}
