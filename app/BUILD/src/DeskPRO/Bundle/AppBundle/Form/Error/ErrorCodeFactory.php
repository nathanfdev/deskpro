<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
