<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
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

    /**
     * return $this.
     */
    public function init()
    {
        $this->multiple = $this->field_def->getOption('multiple', false);
        $this->expanded = $this->field_def->getOption('expanded', false);

        return $this;
    }

    /**
     * @return $this
     */
    public function enableMultiple()
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableMultiple()
    {
        $this->multiple = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function renderHtml($data = null, array $templateVars = [])
    {
        if ($data === null) {
            if ($this->field_def->isRadio() && $this->field_def->getOption('none_choice')) {
                return $this->field_def->getOption('none_choice_title') ?: 'None';
            }

            return '';
        }

        $data['value'] = $this->_getRenderableString($data);

        return parent::renderHtml($data, $templateVars);
    }

    /**
     * {@inheritdoc}
     */
    public function renderText($data = null, array $templateVars = [])
    {
        if ($data === null) {
            if ($this->field_def->isRadio() && $this->field_def->getOption('none_choice')) {
                return $this->field_def->getOption('none_choice_title') ?: 'None';
            }

            return '';
        }

        $data['value'] = $this->_getRenderableString($data);

        return parent::renderText($data, $templateVars);
    }

    /**
     * @param $data
     *
     * @return void|string
     */
    protected function _getRenderableString($data)
    {
        $val      = [];
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

    /**
     * {@inheritdoc}
     */
    public function getFormField($data = null, $availableOnly = false)
    {
        $children = $this->getFieldChildren();
        $choices  = [];
        $selected = [];
        $map      = [];
        // client-side hierarchy
        $root     = [];
        $maxDepth = 1;
        $sortMap  = [];

        foreach ($children as $id => $child) {

            // map for client-side
            $map[$id]        = new \stdClass();
            $map[$id]->id    = $id;
            $map[$id]->title = $child['title'];

            // add choices
            $title        = $child['title'];
            $sortMap[$id] = [$child['display_order']];
            $d            = 1;

            $subChild = $child;
            while ($parent = @$children[$subChild->getOption('parent_id')]) {
                ++$d;
                if ($d > $maxDepth) {
                    $maxDepth = $d;
                }

                $title          = $parent['title'].' > '.$title;
                $sortMap[$id][] = $parent['display_order'];

                $subChild = $parent;
            }
            $sortMap[$id] = array_reverse($sortMap[$id]);
            $choices[$id] = $title;

            // set values
            if (!isset($data['children'][$id]['value'])) {
                continue;
            }
            $selected[] = $id;
        }

        uksort(
            $choices,
            function ($aOpt, $bOpt) use ($sortMap) {
                $aDepth = count($sortMap[$aOpt]);
                $bDepth = count($sortMap[$bOpt]);

                $maxDepth = max($aDepth, $bDepth);

                $an = $bn = 0;
                for ($i = 0; $i < $maxDepth; ++$i) {
                    $an = @$sortMap[$aOpt][$i] ?: 0;
                    $bn = @$sortMap[$bOpt][$i] ?: 0;

                    if ($an != $bn) {
                        break;
                    }
                }

                if ($an == $bn) {
                    return 0;
                }

                return $an < $bn ? -1 : 1;
            }
        );

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
        if ($maxDepth <= 2 && $this->multiple && !$this->expanded) {
            $choices = [];

            foreach ($root as $opt) {
                if (!empty($opt->children)) {
                    $optgroup = [];
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
        $required = defined('DP_INTERFACE')
                    && (
                        ('user' === DP_INTERFACE && $this->field_def->getOption('required'))
                        || ('agent' === DP_INTERFACE && $this->field_def->getOption('agent_required'))
                    );

        $attr                      = $this->field_def->getOption('attr', []);
        $attr['data-map']          = json_encode($root);
        $attr['data-custom-field'] = 'choice-'.($this->expanded ? 'expanded' : 'collapsed').($this->multiple
                ? '-multiple' : null);
        $attr['data-max-depth'] = $maxDepth;

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
                $emptyVal = '---';
            } else {
                $emptyVal = false;
            }
        } else {
            $emptyVal = '';
        }

        // empty value for radio checkboxes
        if ($this->field_def->isRadio() && $this->field_def->getOption('none_choice')) {
            $emptyVal = $this->field_def->getOption('none_choice_title') ?: 'None';
        }

        $fieldOpts = [
            'choices' => $choices,
            // no required for radios because it adds required="required" to HTML,
            // and if they're hidden, Chrome will error-out because it cant focus the element
            // - Its ONLY radios (checks, selects, etc are ok), and ONLY on certain versions of Chrome
            // Note: this is properly fixed anyway in new-portal because the field isnt in the <form> at all
            'required'    => $required && !($this->expanded && !$this->multiple),
            'multiple'    => $this->multiple,
            'expanded'    => $this->expanded,
            'empty_value' => $emptyVal,
            'attr'        => $attr,
        ];

        if (!$this->multiple) {
            // selected value for single select
            $selected = reset($selected) ?: null;
        }

        return App::getFormFactory()->createNamedBuilder(
            $this->getFormFieldName(),
            'choice',
            $selected,
            $fieldOpts
        );
    }

    /**
     * @param array $formData
     *
     * @return mixed|null
     */
    private function findValue(array $formData)
    {
        $names = $this->getAllFormFieldNames();
        foreach ($names as $name) {
            if (!empty($formData[$name])) {
                return $formData[$name];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFromForm(array $formData)
    {
        $value = $this->findValue($formData);

        if ($value) {
            if (is_array($value)) {
                // Multiple selections in the form of field_1[] = childid
                $ret = [];
                foreach ($value as $k) {
                    $ret[] = [$k, 'value', 1];
                }
            } else {
                // Single selections in the form of field_1 = childid
                $ret = [
                    [$value, 'value', 1],
                ];
            }

            return $ret;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function validateFormData(array $formData, $context = self::CONTEXT_USER, $contextData = null)
    {
        $data = isset($formData[$this->getFormFieldName()]) ? $formData[$this->getFormFieldName()] : [];

        // Single-selections dont come in as arrays,
        // but we treat them the same so need this casting
        if (!is_array($data)) {
            $data = [$data];
        }

        $data = Arrays::func($data, ['Orb\Util\Strings', 'trimWhitespace']);
        $data = Arrays::removeFalsey($data);

        // - Choice values are always ints
        // But if a multi-select is sent via JS in some old JS code
        // it's possible a JS null value is sent, which when sent as a POST
        // to PHP becomes the string 'null', which in turn will become a validation error
        // - So this is removing those possible 'null' strings
        $data = array_filter(
            $data,
            function ($d) {
                return $d !== 'null';
            }
        );

        //------------------------------
        // Validate selections
        //------------------------------

        $children        = $this->getFieldChildren();
        $parentOptionIds = [];

        foreach ($children as $c) {
            if ($pid = $c->getOption('parent_id')) {
                $parentOptionIds[$pid] = $pid;
            }
        }

        foreach ($data as $id) {
            if (!is_numeric($id) || !isset($children[$id]) || isset($parentOptionIds[$id])) {
                return $this->makeErrorArray(['invalid_choice']);
            }
        }

        //------------------------------
        // Validate options
        //------------------------------

        $optPrefix = '';
        if ($context == self::CONTEXT_AGENT) {
            $optPrefix = 'agent_';
        }

        $options = [];
        foreach (['required', 'min_length', 'max_length'] as $k) {
            $options[$k] = $this->field_def->getOption($optPrefix.$k);
        }

        // Without required there are no requirements
        if (!$options['required']) {
            return [];
        }

        if ($options['min_length'] && count($data) < $options['min_length']) {
            if ($options['min_length'] == 1 || count($data) === 0) {
                return $this->makeErrorArray(['required']);
            } else {
                return $this->makeErrorArray(['min_length']);
            }
        }

        if ($options['max_length'] && count($data) > $options['max_length']) {
            return $this->makeErrorArray(['max_length']);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function renderFormHtml($formView, array $templateVars = [])
    {
        // In the agent interface, we render single instances of forms many times
        // and that screws up the IDs used in the markup

        // we need this hack to generate unique IDs for expanded choice fields
        // see also Application/DeskPRO/Resources/views/Form/form_div_layout.html.twig - choice_widget_expanded

        $html = parent::renderFormHtml($formView, $templateVars);

        if ($this->expanded) {
            $randId = uniqid('dp_').'_';
            $html   = preg_replace('#<label([^>]+)for="DP_BASE_ID_#', '<label$1for="'.$randId, $html);
            $html   = preg_replace('#<input([^>]+)id="DP_BASE_ID_#', '<input$1id="'.$randId, $html);
        }

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCapabilities()
    {
        return ['is', 'not', 'isset', 'not_isset'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterCapabilities()
    {
        return ['is', 'not'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchType()
    {
        return 'id';
    }
}
