<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Form;

/**
 * A helper that helps build and respond to the DeskPRO/InlineEdit.js system.
 */
class InlineEditHandler
{
    /**
     * Input data.
     *
     * @var array
     */
    protected $input_data;

    /**
     * A flat array of names we got from input.
     * array(person => array(basic => array(fullname => xxx, nickname => xxx)))
     * becomes
     * array(person.basic.full_name, person.basic.nickname).
     *
     * @var array
     */
    protected $got_fields = [];

    /**
     * @param array $input_data This is the 'data' item of the incoming request
     */
    public function __construct(array $input_data = [])
    {
        $this->input_data = $input_data;
    }

    protected function _scanInputFieldNames(array $input, $key_parts = [])
    {
        foreach ($input as $k => $v) {
            $key_parts[] = $k;
            if (is_array($v)) {
                $this->_scanInputFieldNames($v, $key_parts);
            } else {
                $this->got_fields[] = implode('.', $key_parts);
            }
            array_pop($key_parts);
        }
    }

    /**
     * Apply input to a form.
     *
     * @param \Orb\Form\Field\FieldGroup $form
     */
    public function applyToForm(\Orb\Form\Field\FieldGroup $form)
    {
        $form->setFormData($this->input_data);
    }
}
