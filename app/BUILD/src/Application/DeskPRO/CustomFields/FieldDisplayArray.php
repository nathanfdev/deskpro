<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\Handler\Choice;
use Application\DeskPRO\CustomFields\Handler\Date;
use Application\DeskPRO\CustomFields\Handler\DateTime;
use Application\DeskPRO\Entity\CustomDefAbstract;

class FieldDisplayArray implements \ArrayAccess
{
    /**
     * @var FieldManager
     */
    protected $field_manager;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefAbstract
     */
    protected $field_def;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string|null
     */
    protected $field_group = null;

    /**
     * @var bool
     */
    protected $use_default = false;

    public function __construct(FieldManager $field_manager, CustomDefAbstract $field_def, $field_data = [], $field_group = null, $use_default = false)
    {
        $this->field_manager = $field_manager;
        $this->field_def     = $field_def;
        $this->field_group   = $field_group;
        $this->use_default   = $use_default;

        $value = !empty($field_data[$field_def['id']]) && $field_data[$field_def['id']] !== 0 && $field_data[$field_def['id']] !== '0' ? $field_data[$field_def['id']] : null;

        $default_value = $field_def->default_value;
        if ($field_def->getTypeName() == 'hidden') {
            if ($field_def->getOption('cookie_name') && !empty($_COOKIE[$field_def->getOption('cookie_name')])) {
                $default_value = $_COOKIE[$field_def->getOption('cookie_name')];
            } elseif ($field_def->getOption('param_name') && !empty($_REQUEST[$field_def->getOption('param_name')])) {
                $default_value = $_REQUEST[$field_def->getOption('param_name')];
            }
        }

        if ($value === null && $use_default && $default_value) {
            if ($field_def['handler_class'] == 'Application\\DeskPRO\\CustomFields\\Handler\\Choice') {
                $value = ['children' => []];
                if (!is_array($default_value)) {
                    $default_value = explode(',', $default_value);
                }
                foreach ($default_value as $v) {
                    $value['children'][$v] = ['value' => 1];
                }
            } else {
                $value = ['value' => $default_value];
            }
        }
        if (!$field_def->isFormField()) {
            $value = [];
        }

        $this->data = [
            'elId'          => \Orb\Util\Util::requestUniqueIdString(),
            'hasValue'      => ($value !== null),
            'id'            => $field_def->getId(),
            'name'          => 'field_'.$field_def->getId(),
            'title'         => $field_def->getTitle(),
            'value'         => $value,
            'field_handler' => strtolower(\Orb\Util\Util::getBaseClassname($field_def->getHandler())),
        ];
    }

    public function initValue($offset)
    {
        switch ($offset) {
            case 'field_def':
                $this->data['field_def'] = $this->field_def;
                break;

            case 'handler':
                $this->data['handler'] = $this->field_def->getHandler();
                break;

            case 'form':
            case 'formView':
                $field_group = $this->field_group;
                if (!$field_group) {
                    $field_group = App::get('form.factory')->createNamedBuilder('custom_fields', 'form');
                }
                $handler = $this->field_def->getHandler();

            $f = $handler->getFormField($this->data['value']);

                if ($field_group) {
                    $did_add = false;
                    if (!$field_group->has($this->data['name'])) {
                        $did_add = true;
                        $field_group->add($f);
                    }

                    $form     = $field_group->getForm();
                    $formView = $form->createView();
                    $formView = $formView[$this->data['name']];

                    // Remove the field
                    if ($did_add) {
                        $field_group->remove($f->getName());
                    }
                } else {
                    $form     = $f->getForm();
                    $formView = $form->createView();
                }

                $this->data['form']     = $form;
                $this->data['formView'] = $formView;
                break;

            case 'formViewCriteria':
                    $handler = $this->field_def->getHandler();

                if ($handler instanceof Choice && !$this->field_def->getOption('multiple')) {
                    $handler->enableMultiple();

                    $field_group = $this->field_group;
                    if (!$field_group) {
                        $field_group = App::get('form.factory')->createNamedBuilder('custom_fields', 'form');
                    }

                    $f = $handler->getFormField($this->data['value'], true);

                    if ($field_group) {
                        if (!$field_group->has($this->data['name'])) {
                            $field_group->add($f);
                        }

                        $form     = $field_group->getForm();
                        $formView = $form->createView();
                        $formView = $formView[$this->data['name']];
                    } elseif ($handler instanceof Date) {
                    } else {
                        $form     = $f->getForm();
                        $formView = $form->createView();
                    }

                    $this->data['formViewCriteria'] = $formView;

                    $handler->disableMultiple();
                } elseif ($handler instanceof Date || $handler instanceof DateTime) {
                    $this->data['form']             = $handler->getSearchCriteriaForm($this->data['value']);
                    $this->data['formView']         = $this->data['form']->createView();
                    $this->data['formViewCriteria'] = $this->data['formView'];
                } else {
                    $this->initValue('formView');
                    $this->data['formViewCriteria'] = $this->data['formView'];
                }
                break;
        }
    }

    public function offsetExists($offset)
    {
        if (!isset($this->data[$offset])) {
            $this->initValue($offset);
        }

        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!isset($this->data[$offset])) {
            $this->initValue($offset);
        }

        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function mergeArray(array $array)
    {
        array_merge($this->data, $array);
    }

    public function toArray()
    {
        return $this->data;
    }
}
