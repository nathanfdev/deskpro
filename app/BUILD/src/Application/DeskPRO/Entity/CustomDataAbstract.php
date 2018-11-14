<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

/**
 * Base class used for storing custom field data.
 *
 * @method $this setField(CustomDefAbstract $field)
 * @method CustomDefAbstract getField()
 * @method $this setRootField(CustomDefAbstract $root_field)
 * @method CustomDefAbstract getRootField()
 *
 * @property CustomDefAbstract $field
 * @property CustomDefAbstract $root_field
 */
abstract class CustomDataAbstract extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id;

    /**
     * User numeric data.
     *
     * @var int
     */
    protected $value = 0;

    /**
     * User string data.
     *
     * @var string
     */
    protected $input = '';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->setData($value);
        $this->setModelField('value', $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return (int) $this->value;
    }
    /**
     * @param string $input
     *
     * @return $this
     */
    public function setInput($input)
    {
        $this->setData($input);
        $this->setModelField('input', $input);

        return $this;
    }

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Get the value or input.
     *
     * @return mixed
     */
    public function getData()
    {
        if (!$this->field) {
            return '';
        }

        switch ($this->field->getTypeName()) {
            case CustomDefAbstract::TYPE_FILE:
            case CustomDefAbstract::TYPE_TOGGLE:
                return $this->value;
            case CustomDefAbstract::TYPE_CURRENCY:
                if (!$this->input && $this->value) {
                    // backwards compat: no input but a value
                    return $this->value;
                }

                return (int) $this->input;
            default:
                return $this->value ? $this->value : $this->input;
        }
    }

    /**
     * Value or input SQL select clause.
     *
     * This method is a translation of the $this->getData() method into SQL.
     *
     * @param string $data Data table alias
     * @param string $def  Def table alias
     *
     * @return string
     *
     * @deprecated
     */
    public static function getDataSql($data = 'custom_data_ticket', $def = 'custom_def_ticket')
    {
        $toggleHandlerClass = CustomDefAbstract::HANDLER_CLASS_TOGGLE;

        $sql = "
            CASE
                WHEN $def.handler_class = '$toggleHandlerClass' THEN $data.value
                ELSE IF($data.value, $data.value, $data.input)
            END
        ";
        $sql = str_replace("\n", ' ', $sql);
        $sql = trim($sql);

        return $sql;
    }

    /**
     * Set the value or input (use the individual methods if you don't want auto detection).
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        if ($this->root_field && $this->root_field->getWidgetType() === CustomDefAbstract::TYPE_CURRENCY) {
            // data should be already in integer format but has double or string format
            // so force set to int
            $normalized = (int) (string) $data;
            if ((string) $normalized === (string) $data) {
                $data = $normalized;
            }
        }

        if ($data === '' || $data === null) {
            $this->setModelField('value', '');
            $this->setModelField('input', '');
        } elseif (in_array($this->root_field->getType(), [CustomDefAbstract::TYPE_TOGGLE, CustomDefAbstract::TYPE_CURRENCY])) {
            $this->setModelField('value', (int) $data);
        } elseif (in_array($this->root_field->getType(), [CustomDefAbstract::TYPE_CURRENCY])) {
            // save currency in input (real value as string, used in forms etc)
            // but also value. a string means we can work with it in code as a bigint,
            // but value in db might be truncated, but we do this so can use db ops on it
            // in most cases. if column is bigint then it means it'll be fine most of the time anyway.
            $this->setModelField('value', (int) $data);
            $this->setModelField('input', (int) $data);
        } elseif (is_int($data)) {
            $this->setModelField('value', $data);
        } else {
            $this->setModelField('input', (string) $data);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getFieldId()
    {
        return $this->field->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);

        $data['field']      = $this->field->toApiData(false, false, $visited);
        $data['root_field'] = $this->root_field->toApiData(false, false, $visited);

        return $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '[#%s -- %s:%s] %s',
            $this->id ?: '?',
            $this->field->id,
            $this->field->getTypeName(),
            $this->getData()
        );
    }

    /**
     * @return mixed
     */
    abstract public function getOwner();
}
