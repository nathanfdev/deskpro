<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use Symfony\Component\Form\FormInterface;

/**
 * Class FormErrorsGenerator.
 */
class FormErrorsGenerator
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
     * @param string        $codePrefix
     * @param FormInterface $form
     *
     * @return array
     */
    public function generateFormErrors(FormInterface $form, $codePrefix)
    {
        $errors = $list = [];
        foreach ($form->getErrors() as $error) {
            $code   = $this->getFormErrorCode($error);
            $list[] = [
                'code'    => $code,
                'message' => $this->error_message_factory->createFormErrorMessage($codePrefix, $code, $error),
            ];
        }

        if ($list) {
            $errors['errors'] = $list;
        }

        $children = [];
        foreach ($form->all() as $child) {
            if ($child instanceof FormInterface) {
                $child_errors = $this->generateFormErrors($child, $codePrefix);
                if ($child_errors) {
                    $children[$child->getName()] = $child_errors;
                }
            }
        }

        // if it is NOT an associated array, we want to make it one
        if (
            !empty($children) // not empty
            && $this->needsPrefix($children)
        ) {
            $prefix       = !is_numeric($form->getName()) ? $form->getName().'_' : 'field_';
            $new_children = [];
            foreach ($children as $index => $value) {
                $new_children[$prefix.$index] = $value;
            }
            $children = $new_children;
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
        return $this->error_code_factory->getErrorCodeForFormError($error);
    }
}
