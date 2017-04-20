<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Orb\Util\Arrays;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Sometimes we can't use form types but we need to validate entity and generate its errors
 * in the same format as we use for forms.
 *
 * So transform validator errors to form errors output format.
 */
class ValidatorErrorsGenerator
{
    /**
     * @var ErrorCodeFactory
     */
    private $error_code_factory;

    /**
     * @var ErrorMessageFactory
     */
    private $error_message_factory;

    /**
     * Constructor.
     *
     * @param ErrorCodeFactory    $error_code_factory
     * @param ErrorMessageFactory $error_message_factory
     */
    public function __construct(ErrorCodeFactory $error_code_factory, ErrorMessageFactory $error_message_factory)
    {
        $this->error_code_factory    = $error_code_factory;
        $this->error_message_factory = $error_message_factory;
    }

    /**
     * @param ConstraintViolationListInterface|ConstraintViolation[] $violations
     *
     * @return array
     */
    public function generateValidatorErrors(ConstraintViolationListInterface $violations)
    {
        $errors = ['fields' => []];
        foreach ($violations as $violation) {
            $path = preg_split('#[\[\]\.]+#', $violation->getPropertyPath());
            $path = Arrays::removeFalsey($path);

            $this->addError($errors, 'field', $path, $violation);
        }

        return $errors;
    }

    /**
     * @param array               $errors
     * @param string              $numeric_prefix
     * @param array               $path
     * @param ConstraintViolation $violation
     */
    protected function addError(array &$errors, $numeric_prefix, array $path, ConstraintViolation $violation)
    {
        $sub_path = array_shift($path);
        if (is_numeric($sub_path)) {
            $sub_path = $numeric_prefix.'_'.$sub_path;
        }

        if (empty($path)) {
            $code    = $this->error_code_factory->getErrorCodeForConstraintViolation($violation);
            $params  = $this->error_message_factory->parseParams($violation->getParameters());
            $message = $this->error_message_factory->createMessage($code, $params);

            $errors['fields'][$sub_path]['errors'][] = [
                'code'    => $code,
                'message' => $message,
            ];
        } else {
            if (!isset($errors['fields'][$sub_path]['fields'])) {
                $errors['fields'][$sub_path]['fields'] = [];
            }

            $sub_list = &$errors['fields'][$sub_path];

            $this->addError($sub_list, $sub_path, $path, $violation);
        }
    }
}
