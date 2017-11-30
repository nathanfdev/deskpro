<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Component\FilterQueryLanguage;

abstract class AbstractQueryDefinition implements QueryDefinitionInterface
{
    /**
     * @var array
     */
    private $fieldDefs;

    /**
     * @var array
     */
    private $varDefs;

    /**
     * @var array
     */
    private $funcDefs;

    /**
     * A field definition array:.
     *
     * <code>
     * $fieldDefs = [
     *     'field.id' => [
     *         'type' => 'STRING'
     *     ]
     * ];
     * </code>
     *
     * @return array
     */
    abstract public function getFieldDefs();

    /**
     * A field definition array:.
     *
     * <code>
     * $varDefs = [
     *     'var.id' => [
     *         'type' => 'STRING'
     *     ]
     * ];
     * </code>
     *
     * @return array
     */
    public function getVariableDefs()
    {
        return [];
    }

    /**
     * A function definition array:.
     *
     * <code>
     * $funcDefs = [
     *     'myFunc' => [
     *         'fields'    => ['field.id', '*'],
     *         'operators' => ['=', 'IN', '!=', 'NOT_IN'],
     *         'params'    => ['INT', 'INT']
     *     ]
     * ];
     * </code>
     *
     * @return array
     */
    public function getFunctionDefs()
    {
        return [];
    }

    private function initDefs()
    {
        if ($this->fieldDefs === null) {
            $this->fieldDefs = $this->getFieldDefs();
        }
        if ($this->funcDefs === null) {
            $this->funcDefs = $this->getFunctionDefs();
        }
        if ($this->varDefs === null) {
            $this->varDefs = $this->getVariableDefs();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateField($identity)
    {
        $this->initDefs();

        return isset($this->fieldDefs[$identity])
            ? []
            : ['invalid_field' => "Field $identity is not a valid name"];
    }

    /**
     * {@inheritdoc}
     */
    public function validateVariable($varId)
    {
        $this->initDefs();

        return isset($this->varDefs[$varId])
            ? []
            : ['invalid_var' => "Varibale $varId is not a valid name"];
    }

    /**
     * {@inheritdoc}
     */
    public function validateFunctionCall($name, $params, $op, $fieldId)
    {
        $this->initDefs();

        if (!isset($this->funcDefs[$name])) {
            return ['invalid_func' => "Function $name is not a valid name"];
        }

        $def = $this->funcDefs[$name];

        if (!in_array('*', $def['fields']) && !in_array($fieldId, $def['fields'])) {
            return ['func_not_applicable' => "Function $name does not apply to field $fieldId"];
        }

        if (!in_array('*', $def['operators']) && !in_array($op, $def['operators'])) {
            return ['func_invalid_op' => "Function $name cannot be used with the $op operator"];
        }

        return true;
    }
}
