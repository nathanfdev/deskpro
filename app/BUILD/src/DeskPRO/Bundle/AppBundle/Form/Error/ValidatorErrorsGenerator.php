<?php

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
    private $errorCodeFactory;

    /**
     * @var ErrorMessageFactory
     */
    private $errorMessageFactory;

    /**
     * Constructor.
     *
     * @param ErrorCodeFactory    $errorCodeFactory
     * @param ErrorMessageFactory $errorMessageFactory
     */
    public function __construct(ErrorCodeFactory $errorCodeFactory, ErrorMessageFactory $errorMessageFactory)
    {
        $this->errorCodeFactory    = $errorCodeFactory;
        $this->errorMessageFactory = $errorMessageFactory;
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
     * @param string              $numericPrefix
     * @param array               $path
     * @param ConstraintViolation $violation
     */
    protected function addError(array &$errors, $numericPrefix, array $path, ConstraintViolation $violation)
    {
        $subPath = array_shift($path);
        if (is_numeric($subPath)) {
            $subPath = $numericPrefix.'_'.$subPath;
        }

        if (empty($path)) {
            $code    = $this->errorCodeFactory->getErrorCodeForConstraintViolation($violation);
            $params  = $this->errorMessageFactory->parseParams($violation->getParameters());
            $message = $this->errorMessageFactory->createMessage($code, $params);

            $errors['fields'][$subPath]['errors'][] = [
                'code'    => $code,
                'message' => $message,
            ];
        } else {
            if (!isset($errors['fields'][$subPath]['fields'])) {
                $errors['fields'][$subPath]['fields'] = [];
            }

            $subList = &$errors['fields'][$subPath];

            $this->addError($subList, $subPath, $path, $violation);
        }
    }
}
