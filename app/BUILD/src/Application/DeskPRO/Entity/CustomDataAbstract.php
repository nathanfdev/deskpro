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
            case CustomDefAbstract::TYPE_CURRENCY:
            case CustomDefAbstract::TYPE_TOGGLE:
                return $this->value;
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
