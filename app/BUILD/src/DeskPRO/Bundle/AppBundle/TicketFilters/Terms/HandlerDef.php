<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

/**
 * Class HandlerDef.
 */
class HandlerDef
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var array
     */
    private $funcs = [];

    /**
     * @return HandlerDef
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param string $fieldId
     * @param string $operators
     *
     * @return $this
     */
    public function addField($fieldId, $operators = '*')
    {
        $fieldIdKey                = strtolower($fieldId);
        $this->fields[$fieldIdKey] = [
            'name'      => $fieldId,
            'operators' => (array) $operators,
        ];

        return $this;
    }

    /**
     * @param string $name
     * @param string $operators
     * @param string $fieldIds
     *
     * @return $this
     */
    public function addFunction($name, $operators = '*', $fieldIds = '*')
    {
        $nameKey               = strtolower($name);
        $this->funcs[$nameKey] = [
            'name'      => $name,
            'fields'    => (array) $fieldIds,
            'operators' => (array) $operators,
        ];

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFieldIds()
    {
        return array_keys($this->fields);
    }

    /**
     * @return string[]
     */
    public function getFuncNames()
    {
        return array_keys($this->funcs);
    }

    /**
     * Returns a map of fieldId => [operators => []].
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns a map of funcName => [fields => [], operators => []].
     *
     * @return array
     */
    public function getFunctions()
    {
        return $this->funcs;
    }

    /**
     * Check if a field has been registered.
     *
     * @param string $fieldId
     *
     * @return bool
     */
    public function hasField($fieldId)
    {
        $fieldIdKey = strtolower($fieldId);

        return isset($this->fields[$fieldIdKey]);
    }

    /**
     * Check if a field supports a particular operator.
     *
     * @param string $fieldId
     * @param string $operator
     *
     * @return bool
     */
    public function canHandleFieldTerm($fieldId, $operator)
    {
        $fieldIdKey = strtolower($fieldId);

        return isset($this->fields[$fieldIdKey]) && (
            in_array('*', $this->fields[$fieldIdKey]['operators'])
            || in_array($operator, $this->fields[$fieldIdKey]['operators'])
        );
    }

    /**
     * Check if a function has been registered.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasFunction($name)
    {
        $nameKey = strtolower($name);

        return isset($this->funcs[$nameKey]);
    }

    /**
     * Check if a function supports a particular field and operator.
     *
     * @param string $name
     * @param string $fieldId
     * @param string $operator
     *
     * @return bool
     */
    public function canHandleFieldFunc($name, $fieldId, $operator)
    {
        $nameKey = strtolower($name);

        return isset($this->funcs[$nameKey]) && (
                in_array('*', $this->funcs[$nameKey]['fields'])
                || in_array($fieldId, $this->funcs[$nameKey]['fields'])
            ) && (
                in_array('*', $this->funcs[$nameKey]['operators'])
                || in_array($operator, $this->funcs[$nameKey]['operators'])
            );
    }

    /**
     * Gets the real name (i.e. before normalization).
     *
     * @param string $name
     *
     * @return string
     */
    public function getDefinedFuncName($name)
    {
        $nameKey = strtolower($name);

        return $this->funcs[$nameKey]['name'];
    }

    /**
     * Gets the real name (i.e. before normalization).
     *
     * @param string $fieldId
     *
     * @return string
     */
    public function getDefinedFieldName($fieldId)
    {
        $fieldIdKey = strtolower($fieldId);

        return $this->fields[$fieldIdKey]['name'];
    }
}
