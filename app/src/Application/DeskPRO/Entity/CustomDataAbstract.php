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
 *
 * @category Entities
 */
namespace Application\DeskPRO\Entity;

/**
 * Base class used for storing custom field data.
 *
 * @method $this setField(CustomDefAbstract $field)
 * @method $this setRootField(CustomDefAbstract $root_field)
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
    protected $id = null;

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

    public function __construct()
    {
        $this->input = '';

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     * @return $this
     */
    public function setValue($value)
    {
        $this->setModelField('value', $value);

        return $this;
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
     * Get the value or input.
     *
     * @return mixed
     */
    public function getData()
    {
        $type = !empty($this->field) ? $this->field->getTypeName() : 'text';

        switch ($type) {
            case 'toggle':
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
     */
    public function setData($data)
    {
        if (is_int($data)) {
            $this->setModelField('value', $data);
        } else {
            $this->setModelField('input', (string) $data);
        }
    }

    public function getValue()
    {
        return (int) $this->value;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function getFieldId()
    {
        return $this->field->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = array())
    {
        $data = parent::toApiData($primary, $deep, $visited);

        // record isn't useful without these, so always include them
        if ($this->field) {
            $data['field'] = $this->field->toApiData(false, false, $visited);
        }
        if ($this->root_field) {
            $data['root_field'] = $this->root_field->toApiData(false, false, $visited);
        }

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
            $this->field ? $this->field->id : '?',
            $this->field ? $this->field->getTypeName() : 'unknown',
            $this->getData()
        );
    }
}
