<?php

namespace DeskPRO\Bundle\AppBundle\Form\Error;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormErrorsGenerator.
 */
class FormErrorsGenerator
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
     * @param FormInterface $form
     *
     * @return array
     */
    public function generateFormErrors(FormInterface $form)
    {
        $errors = $list = [];
        foreach ($form->getErrors() as $error) {
            $code   = $this->getFormErrorCode($error);
            $list[] = [
                'code'    => $code,
                'message' => $this->errorMessageFactory->createFormErrorMessage($code, $error),
            ];
        }

        if ($list) {
            $errors['errors'] = $list;
        }

        $children = [];
        foreach ($form->all() as $child) {
            if ($child instanceof FormInterface) {
                $childErrors = $this->generateFormErrors($child);
                if ($childErrors) {
                    $children[$child->getName()] = $childErrors;
                }
            }
        }

        // if it is NOT an associated array, we want to make it one
        if (
            !empty($children) // not empty
            && $this->needsPrefix($children)
        ) {
            $prefix      = !is_numeric($form->getName()) ? $form->getName().'_' : 'field_';
            $newChildren = [];
            foreach ($children as $index => $value) {
                $newChildren[$prefix.$index] = $value;
            }
            $children = $newChildren;
        }

        if ($children) {
            $errors['fields'] = $children;
        }

        return $errors;
    }

    /**
     * @param array $children
     *
     * @return bool
     */
    protected function needsPrefix(array $children)
    {
        if (array_keys($children) === range(0, count($children) - 1)) {
            // indexed array, needs prefix
            return true;
        }

        foreach ($children as $key => $val) {
            if (!is_numeric($key)) {
                // any non-numeric key means no prefix
                return false;
            }
        }

        // if we get here all keys are numeric, so needs prefixing
        return true;
    }

    /**
     * @param FormError $error
     *
     * @return mixed|string
     */
    protected function getFormErrorCode(FormError $error)
    {
        return $this->errorCodeFactory->getErrorCodeForFormError($error);
    }
}
