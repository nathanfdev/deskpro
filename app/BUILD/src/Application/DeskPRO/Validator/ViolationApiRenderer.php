<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Validator;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ViolationApiRenderer
{
    /**
     * @var array
     */
    private $aliases = [];

    public function __construct()
    {
        // Default aliases
        $this->addClassCodeName('Application\\DeskPRO\\Entity', 'Entity');
    }

    /**
     * Adds a codename for a class (or namespace).
     *
     * @param string $classname
     * @param string $codename
     */
    public function addClassCodeName($classname, $codename)
    {
        $this->aliases[$classname] = $codename;
    }

    /**
     * Gets the codename for a class.
     *
     * @param string $classname
     *
     * @return string
     */
    public function getCodeName($classname)
    {
        $parts = explode('\\', $classname);
        $alias = null;
        $ends  = [];
        do {
            $part_string = implode('\\', $parts);
            if (isset($this->aliases[$part_string])) {
                $alias = $this->aliases[$part_string];
                break;
            }
            $ends[] = array_pop($parts);
        } while ($parts);

        if ($alias) {
            if ($ends) {
                $alias .= '\\'.implode('\\', $ends);
            }
        } else {
            $alias = $classname;
        }

        return str_replace('\\', '.', $alias);
    }

    /**
     * @param ConstraintViolation $err
     *
     * @return array
     */
    public function renderViolation(ConstraintViolation $err)
    {
        $val = $err->getRoot();
        if (is_object($val)) {
            if ($val instanceof \Doctrine\ORM\Proxy\Proxy) {
                $classname = get_parent_class($val);
            } else {
                $classname = get_class($val);
            }

            $field_id = $this->getCodeName($classname);
        } else {
            $field_id = $err->getPropertyPath();
        }

        $code    = $err->getCode();
        $message = $err->getMessage();

        if (preg_match('#^\[([a-zA-Z0-9\-_\.]+)\](.*)$#', $message, $m)) {
            $message = trim($m[2]);

            if (!$code) {
                $code = $m[1];
            }
        }

        if (!$code) {
            $code = 'undefined';
        }

        return [
            'field_id'  => $field_id,
            'prop_path' => $err->getPropertyPath(),
            'prop'      => $err->getPropertyPath(),
            'code'      => $code,
            'message'   => $message,
        ];
    }

    /**
     * Renders a list of validation errors.
     *
     * @param ConstraintViolationList $list
     *
     * @return array
     */
    public function renderViolationList(ConstraintViolationList $list)
    {
        $info = [
            'errors'      => [],
            'error_codes' => [],
        ];

        foreach ($list as $err) {
            $err_info              = $this->renderViolation($err);
            $info['errors'][]      = $err_info;
            $info['error_codes'][] = $err_info['prop'].'.'.$err_info['code'];
        }

        return $info;
    }

    /**
     * Renders multiple violations into a single list. Ideal if you have different components using different validators, but you need
     * to return a single validation error response.
     *
     * @param ConstraintViolationList[] $lists A key=>ConstraintViolationList map, where the key is used to prefix the paths of the errors in the list
     *
     * @return array
     */
    public function renderCombinedViolationList(array $lists)
    {
        $mega_list = [
            'errors'      => [],
            'error_codes' => [],
        ];

        foreach ($lists as $prefix => $list) {
            $info = $this->renderViolationList($list);

            foreach ($info['errors'] as $err) {
                $err['prop']           = $prefix.'.'.$err['prop'];
                $mega_list['errors'][] = $err;
            }

            foreach ($info['error_codes'] as $err_code) {
                $mega_list['error_codes'] = $prefix.'.'.$err_code;
            }
        }

        return $mega_list;
    }

    /**
     * @param FormError $err
     *
     * @return array
     */
    public function renderFormError(FormError $err)
    {
        $code    = null;
        $message = $err->getMessage();
        if (preg_match('#^\[([a-zA-Z0-9\-_\.]+)\](.*)$#', $message, $m)) {
            $message = trim($m[2]);

            if (!$code) {
                $code = $m[1];
            }
        }

        if (!$code) {
            $code = 'undefined:'.$err->getMessage();
        }

        return [
            'code'    => $code,
            'message' => $message,
        ];
    }

    /**
     * Renders a list of errors from a form.
     *
     * Returns an array of:
     * - errors: A flat array of all errors in the form
     * - error_codes: A flat array of all error codes
     * - error_codes_grouped: An array of error codes per field in the form.
     *   For example, if there is a field myform[something][deep][email] that is invalid,
     *   then myform will have the error code 'email'. myform.something will have 'email'. myform.something.deep will have 'myform'.
     *   This is ideal for use in templates when you just want to know if some field is invalid without being too specific.
     *   Like if a form only has that single email field, then its easier to do if(error_codes_grouped.myform has email) rather than if(error_codes has myform.something.deep.email.email)
     *
     * @param Form        $form
     * @param string|null $parent_path (internal use)
     *
     * @return array
     */
    public function renderFormErrorList(Form $form, $parent_path = null)
    {
        $info = [
            'errors'              => [],
            'error_codes'         => [],
            'error_codes_grouped' => [],
        ];

        $name = $form->getName();
        $path = $parent_path ? $parent_path.'.' : '';
        $path .= $form->getPropertyPath() ? $form->getPropertyPath()->__toString() : $name;
        $path = preg_replace('#\[(\d+)\]#', '$1', $path);

        foreach ($form->getErrors() as $err) {
            $err_info = $this->renderFormError($err);

            $err_info['field_id']  = $name;
            $err_info['prop']      = $path;
            $err_info['prop_path'] = $path;

            $info['errors'][]      = $err_info;
            $info['error_codes'][] = $err_info['prop'].'.'.$err_info['code'];
        }

        foreach ($form->all() as $sub_form) {
            $sub_errors = $this->renderFormErrorList($sub_form, $path);

            if ($sub_errors['errors'] || $sub_errors['error_codes']) {
                $info['errors']              = array_merge($info['errors'],              $sub_errors['errors']);
                $info['error_codes']         = array_merge($info['error_codes'],         $sub_errors['error_codes']);
                $info['error_codes_grouped'] = array_merge($info['error_codes_grouped'], $sub_errors['error_codes_grouped']);
            }
        }

        if (!isset($info['error_codes_grouped'][$path])) {
            $info['error_codes_grouped'][$path] = [];
        }
        foreach ($info['errors'] as $err) {
            $info['error_codes_grouped'][$path][] = $err['code'];
        }
        if (!empty($info['error_codes_grouped'][$path])) {
            $info['error_codes_grouped'][$path] = array_values(array_unique($info['error_codes_grouped'][$path]));
        }

        return $info;
    }
}
