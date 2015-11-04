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
namespace Application\DeskPRO\CustomFields\Handler;

use Application\DeskPRO\App;
use Orb\Util\Arrays;

/**
 * Handles the choice field.
 */
class Choice extends HandlerAbstract
{
    /** @var bool */
    protected $multiple = false;
    /** @var bool */
    protected $expanded = false;

    public function init()
    {
        $this->multiple = $this->field_def->getOption('multiple', false);
        $this->expanded = $this->field_def->getOption('expanded', false);
    }

    public function enableMultiple()
    {
        $this->multiple = true;
    }

    public function disableMultiple()
    {
        $this->multiple = false;
    }

    public function renderHtml($data = null, array $template_vars = array())
    {
        if ($data === null) {
            return '';
        }

        $data['value'] = $this->_getRenderableString($data);

        return parent::renderHtml($data, $template_vars);
    }

    public function renderText($data = null, array $template_vars = array())
    {
        if ($data === null) {
            return '';
        }

        $data['value'] = $this->_getRenderableString($data);

        return  parent::renderText($data, $template_vars);
    }

    protected function _getRenderableString($data)
    {
        $val      = array();
        $children = $this->getFieldChildren();

        if (!isset($data['children'])) {
            return;
        }

        foreach ($data['children'] as $id => $v) {
            if (!isset($v['value']) || (!$child = @$children[$id])) {
                continue;
            }

            $title = $child['title'];
            // full path
            while ($parent = @$children[$child->getOption('parent_id')]) {
                $title = $parent['title'].' > '.$title;
                $child = $parent;
            }
            $val[] = $title;
        }

        return implode(', ', $val);
    }

    public function getFormField($data = null, $availableOnly = false)
    {
        $children = $this->getFieldChildren();
        $choices  = array();
        $selected = array();
        $map      = array();
        // client-side hierarchy
        $root      = array();
        $max_depth = 1;
        $sort_map  = array();

        foreach ($children as $id => $child) {

            // map for client-side
            $map[$id]        = new \StdClass();
            $map[$id]->id    = $id;
            $map[$id]->title = $child['title'];

            // add choices
            $title         = $child['title'];
            $sort_map[$id] = array($child['display_order']);
            $d             = 1;

            $sub_child = $child;
            while ($parent = @$children[$sub_child->getOption('parent_id')]) {
                ++$d;
                if ($d > $max_depth) {
                    $max_depth = $d;
                }

                $title           = $parent['title'].' > '.$title;
                $sort_map[$id][] = $parent['display_order'];

                $sub_child = $parent;
            }
            $sort_map[$id] = array_reverse($sort_map[$id]);
            $choices[$id]  = $title;

            // set values
            if (!isset($data['children'][$id]['value'])) {
                continue;
            }
            $selected[] = $id;
        }

        uksort($choices, function ($a_opt, $b_opt) use ($sort_map) {
            $a_depth = count($sort_map[$a_opt]);
            $b_depth = count($sort_map[$b_opt]);

            $max_depth = max($a_depth, $b_depth);

            $an = $bn = 0;
            for ($i = 0; $i < $max_depth; ++$i) {
                $an = @$sort_map[$a_opt][$i] ?: 0;
                $bn = @$sort_map[$b_opt][$i] ?: 0;

                if ($an != $bn) {
                    break;
                }
            }

            if ($an == $bn) {
                return 0;
            }

            return $an < $bn ? -1 : 1;
        });

        // map for client-side
        foreach ($children as $id => $child) {
            if ($parent = @$map[$child->getOption('parent_id')]) {
                $parent->children[] = $map[$id];
                if (isset($choices[$parent->id])) {
                    unset($choices[$parent->id]);
                }
            } else {
                $root[] = @$map[$id];
            }
        }

        // For max 2-level multi-select, use optgroups
        if ($max_depth <= 2 && $this->multiple && !$this->expanded) {
            $choices = array();

            foreach ($root as $opt) {
                if (!empty($opt->children)) {
                    $optgroup = array();
                    foreach ($opt->children as $sub_opt) {
                        $optgroup[$sub_opt->id] = $sub_opt->title;
                    }
                    $choices[$opt->title] = $optgroup;
                } else {
                    $choices[$opt->id] = $opt->title;
                }
            }
        }

        // required
        $required = defined('DP_INTERFACE') && (
            ('user' === DP_INTERFACE && $this->field_def->getOption('required'))
            ||
            ('agent' === DP_INTERFACE && $this->field_def->getOption('agent_required'))
        );

        $attr                      = $this->field_def->getOption('attr', array());
        $attr['data-map']          = json_encode($root);
        $attr['data-custom-field'] = 'choice-'.($this->expanded ? 'expanded' : 'collapsed').($this->multiple ? '-multiple' : null);
        $attr['data-max-depth']    = $max_depth;

        if ($class = $this->field_def->getOption('custom_css_classname')) {
            $attr['class'] = @$attr['class'].' '.$class;
        }

        if (!$this->multiple) {
            // turns off legacy select2 handler
            $attr['data-no-select2'] = 1;
        }

        // - We need to always have a default blank
        // option for backwards compat with lots of layout/UI code
        // - Without a blank option, browser will send option1 along with any
        // form, resulting in a value save when there shouldnt be
        // (because client-side, we simply display:none fields that dont apply, but browser
        // will still have field values for them; we need a blank option to send in a case like that).
        if ($this->expanded) {
            if ($selected && $required) {
                $empty_val = '---';
            } else {
                $empty_val = false;
            }
        } else {
            $empty_val = '';
        }

        $field_opts = array(
            'choices'     => $choices,
            'required'    => $required,
            'multiple'    => $this->multiple,
            'expanded'    => $this->expanded,
            'empty_value' => $empty_val,
            'attr'        => $attr,
        );

        if (!$this->multiple) {
            // selected value for single select
            $selected = reset($selected) ?: null;
        }

        return App::getFormFactory()->createNamedBuilder(
            $this->getFormFieldName(),
            'choice',
            $selected,
            $field_opts
        );
    }

    public function getDataFromForm(array $form_data)
    {
        $name = $this->getFormFieldName();

        $value = null;
        if (!empty($form_data[$name])) {
            $value = $form_data[$name];
        }

        if ($value) {
            if (is_array($value)) {
                // Multiple selections in the form of field_1[] = childid
                $ret = array();
                foreach ($value as $k) {
                    $ret[] = array($k, 'value', 1);
                }
            } else {
                // Single selections in the form of field_1 = childid
                $ret = array(
                    array($value, 'value', 1),
                );
            }

            return $ret;
        }

        return array();
    }

    public function validateFormData(array $form_data, $context = self::CONTEXT_USER, $context_data = null)
    {
        $data = isset($form_data[$this->getFormFieldName()]) ? $form_data[$this->getFormFieldName()] : array();

        // Single-selections dont come in as arrays,
        // but we treat them the same so need this casting
        if (!is_array($data)) {
            $data = array($data);
        }

        $data = Arrays::func($data, array('Orb\Util\Strings', 'trimWhitespace'));
        $data = Arrays::removeFalsey($data);

        // - Choice values are always ints
        // But if a multi-select is sent via JS in some old JS code
        // it's possible a JS null value is sent, which when sent as a POST
        // to PHP becomes the string 'null', which in turn will become a validation error
        // - So this is removing those possible 'null' strings
        $data = array_filter($data, function ($d) {
            return $d !== 'null';
        });

        #------------------------------
        # Validate selections
        #------------------------------

        $children          = $this->getFieldChildren();
        $parent_option_ids = array();

        foreach ($children as $c) {
            if ($pid = $c->getOption('parent_id')) {
                $parent_option_ids[$pid] = $pid;
            }
        }

        foreach ($data as $id) {
            if (!is_numeric($id) || !isset($children[$id]) || isset($parent_option_ids[$id])) {
                return $this->makeErrorArray(array('invalid_choice'));
            }
        }

        #------------------------------
        # Validate options
        #------------------------------

        $opt_prefix = '';
        if ($context == self::CONTEXT_AGENT) {
            $opt_prefix = 'agent_';
        }

        $options = array();
        foreach (array('required', 'min_length', 'max_length') as $k) {
            $options[$k] = $this->field_def->getOption($opt_prefix.$k);
        }

        // Without required there are no requirements
        if (!$options['required']) {
            return array();
        }

        if ($options['min_length'] && count($data) < $options['min_length']) {
            if ($options['min_length'] == 1) {
                return $this->makeErrorArray(array('required'));
            } else {
                return $this->makeErrorArray(array('min_length'));
            }
        }

        if ($options['max_length'] && count($data) > $options['max_length']) {
            return $this->makeErrorArray(array('max_length'));
        }

        return array();
    }

    public function getSearchCapabilities()
    {
        return array('is', 'not');
    }

    public function getFilterCapabilities()
    {
        return array('is', 'not');
    }

    public function getSearchType()
    {
        return 'id';
    }
}
