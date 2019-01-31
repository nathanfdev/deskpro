<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

use Orb\Util\Arrays;

class ChoiceField extends CustomFieldAbstract
{
    /** @var bool */
    public $multiple = false;
    /** @var bool */
    public $expanded = false;
    /** @var int */
    public $min_length;
    /** @var int */
    public $max_length;

    /** @var int */
    public $agent_min_length;
    /** @var int */
    public $agent_max_length;

    /** @var string|null */
    public $field_type = null;
    /** @var string */
    public $choices_structure = '';
    /** @var string */
    public $choices_removed_structure = '';
    /** @var mixed */
    public $default_value = null;

    /**
     * @var bool
     */
    public $none_choice = false;

    /**
     * @var string
     */
    public $none_choice_title = '';

    protected function init()
    {
        if ($this->_field->default_value) {
            $this->default_value = $this->_field->default_value;
        }

        if ($this->_field->getOption('multiple')) {
            $this->multiple = true;
        }
        if ($this->_field->getOption('expanded')) {
            $this->expanded = true;
        }

        if ($this->_field->getOption('min_length')) {
            $this->validation_type = 'required';
            $this->required        = true;
            $this->min_length      = $this->_field->getOption('min_length');
        }
        if ($this->_field->getOption('max_length')) {
            $this->validation_type = 'required';
            $this->required        = true;
            $this->max_length      = $this->_field->getOption('max_length');
        }

        if ($this->_field->getOption('agent_min_length')) {
            $this->agent_validation_type = 'required';
            $this->agent_required        = true;
            $this->agent_min_length      = $this->_field->getOption('agent_min_length');
        }
        if ($this->_field->getOption('agent_max_length')) {
            $this->agent_validation_type = 'required';
            $this->agent_required        = true;
            $this->agent_max_length      = $this->_field->getOption('agent_max_length');
        }

        if ($this->_field->getOption('agent_validation_resolve')) {
            $this->agent_validation_resolve = true;
        }

        if ($this->multiple) {
            if ($this->expanded) {
                $this->field_type = 'checkbox';
            } else {
                $this->field_type = 'multi_select';
            }
        } else {
            if ($this->expanded) {
                $this->field_type = 'radio';
            } else {
                $this->field_type = 'select';
            }
        }

        if (!$this->field_type === 'radio') {
            $this->none_choice       = $this->_field->getOption('none_choice');
            $this->none_choice_title = $this->_field->getOption('none_choice_title');
        }

        if (!$this->isNewField()) {
            foreach ($this->_field->children as $child) {
                $this->choices[$child->id] = $child->title;
            }
        }
    }

    public function setFieldType($field_type)
    {
        $this->field_type = $field_type;
        if ($this->field_type == 'checkbox' || $this->field_type == 'radio') {
            $this->expanded = true;
        } else {
            $this->expanded = false;
        }
        if ($this->field_type == 'checkbox' || $this->field_type == 'multi_select') {
            $this->multiple = true;
        } else {
            $this->multiple = false;
        }
    }

    public function save()
    {
        // need to map array to string before entity start listen for changes
        if (is_array($this->default_value)) {
            $this->default_value = implode(',', $this->default_value);
        }
        parent::save();
    }

    protected function setFieldProperties()
    {
        $field = $this->_field;

        $field->setOption('multiple', $this->multiple);
        $field->setOption('expanded', $this->expanded);

        if ($this->min_length || $this->max_length) {
            $this->validation_type = 'required';
            $field->setOption('required', true);
            $field->setOption('min_length', $this->min_length);
            $field->setOption('max_length', $this->max_length);
        } else {
            $this->validation_type = null;
            $field->setOption('required', null);
            $field->setOption('min_length', null);
            $field->setOption('max_length', null);
        }

        if ($this->agent_max_length || $this->agent_min_length) {
            $this->agent_validation_type = 'required';
            $field->setOption('agent_required', true);
            $field->setOption('agent_min_length', $this->agent_min_length);
            $field->setOption('agent_max_length', $this->agent_max_length);
        } else {
            $this->agent_validation_type = null;
            $field->setOption('agent_required', null);
            $field->setOption('agent_min_length', null);
            $field->setOption('agent_max_length', null);
        }

        // radio choice
        if (!$this->multiple && $this->expanded) {
            $field->setOption('none_choice', $this->none_choice);
            $field->setOption('none_choice_title', $this->none_choice_title);
        }

        if (!$this->default_value) {
            $field->default_value = null;
        }
    }

    protected function saveAdditional()
    {
        /*
         This array looks like this:
            {
                "cb_1": {
                    "id": "cb_1",
                    "@is_new": true,
                    "title": "Title",
                    "parent_id": null,
                    "display_order": 0
                }
                ...
            }
         */
        $choices_structure = $this->choices_structure;
        $choices_structure = Arrays::keyFromData($choices_structure, 'id');

        $choices     = [];
        $removed_ids = [];
        foreach ($this->_field->children as $ch) {
            if (!isset($choices_structure[$ch->id])) {
                $this->_em->remove($ch);
                $removed_ids[$ch->id] = true;
            } else {
                $choices[$ch->getId()] = $ch;
            }
        }

        // If there were removed parents, then all children under those parents
        // must be removed as well
        do {
            $changed = false;
            foreach ($choices as $ch) {
                if (@$removed_ids[$ch->getOption('parent_id')] && !@$removed_ids[$ch->id]) {
                    $this->_em->remove($ch);
                    $removed_ids[$ch->id] = true;
                    $changed              = true;
                }
            }
        } while ($changed);

        foreach ($removed_ids as $rid) {
            unset($choices[$rid]);
        }

        // Now add new ones
        foreach ($choices_structure as $cinfo) {
            if (!isset($cinfo['@is_new']) || !$cinfo['@is_new']) {
                continue;
            }

            $ch = $this->_field->createChild();
            $ch->setTitle($cinfo['title']);
            $ch->setOption('cb', str_replace('cb_', '', $cinfo['id']));

            $choices[$cinfo['id']] = $ch;
            $this->_em->persist($ch);
        }

        // Parents need to be flushed now
        // so id's are hooked up properly
        $this->_em->flush();

        // Hook up parents and set display order
        foreach ($choices_structure as $cinfo) {
            if (!isset($choices[$cinfo['id']])) {
                continue;
            }

            $ch = $choices[$cinfo['id']];
            if ($cinfo['parent_id'] && isset($choices[$cinfo['parent_id']]) && $ch->getOption('parent_id') != $choices[$cinfo['parent_id']]->id) {
                $ch->setOption('parent_id', $choices[$cinfo['parent_id']]->id);
            }

            if (!empty($cinfo['title']) && $cinfo['title'] != $ch->title) {
                $ch->title = $cinfo['title'];
            }

            if ($ch->display_order != (int) $cinfo['display_order']) {
                $ch->display_order = (int) $cinfo['display_order'];
            }
            $this->_em->persist($ch);
        }

        $this->_em->flush();

        if ($this->default_value != $this->_field->default_value || false !== strpos($this->default_value, 'cb_')) {
            $this->_field->default_value = null;

            $defaults = [];
            foreach (explode(',', $this->default_value) as $dval) {
                if (isset($choices[$dval])) {
                    $defaults[] = $choices[$dval]->id;
                }
            }
            $this->_field->default_value = implode(',', $defaults);

            $this->_em->persist($this->_field);
            $this->_em->flush();
        }
    }
}
