<?php

namespace DeskPRO\Bundle\AppBundle\Form\Error;

/**
 * Class ExceptionErrorsGenerator.
 */
class ExceptionErrorsGenerator
{
    /**
     * @var ErrorMessageFactory
     */
    private $errorMessageFactory;

    /**
     * Constructor.
     *
     * @param ErrorMessageFactory $errorMessageFactory
     */
    public function __construct(ErrorMessageFactory $errorMessageFactory)
    {
        $this->errorMessageFactory = $errorMessageFactory;
    }

    /**
     * @param string $errorCode
     * @param array  $params
     * @param array  $path
     *
     * @return array
     */
    public function generateByErrorCode($errorCode, array $params = [], array $path = [])
    {
        if (!$path) {
            $errors = [
                'code'    => $errorCode,
                'message' => $this->errorMessageFactory->createMessage($errorCode, $params),
            ];
        } else {
            $errors = ['fields' => []];
            $this->addError($errors, 'field', $path, $errorCode, $params);
        }

        return $errors;
    }

    /**
     * @param array  $errors
     * @param string $numericPrefix
     * @param array  $path
     * @param string $errorCode
     * @param array  $params
     */
    protected function addError(array &$errors, $numericPrefix, array $path, $errorCode, array $params)
    {
        $subPath = array_shift($path);
        if (is_numeric($subPath)) {
            $subPath = $numericPrefix.'_'.$subPath;
        }

        if (empty($path)) {
            $errors['fields'][$subPath]['errors'][] = [
                'code'    => $errorCode,
                'message' => $this->errorMessageFactory->createMessage($errorCode, $params),
            ];
        } else {
            if (!isset($errors['fields'][$subPath]['fields'])) {
                $errors['fields'][$subPath]['fields'] = [];
            }

            $subList = &$errors['fields'][$subPath];

            $this->addError($subList, $subPath, $path, $errorCode, $params);
        }
    }
}
