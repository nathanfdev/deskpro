<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;

class FilterView
{
    const TYPE_LIST = 'list';
    const TYPE_TABLE = 'table';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Person
     */
    protected $agent;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected $icon_fields;

    /**
     * @var array
     */
    protected $options;

    public function __construct()
    {
        $this->type = self::TYPE_LIST;
        $this->icon_fields = array();
        $this->options = array();
        $this->fields = array();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        if (!in_array($type, array(self::TYPE_LIST, self::TYPE_TABLE))) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid filter view type', $type));
        }

        $this->type = $type;
    }

    /**
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param Filter $filter
     */
    public function setFilter(Filter $filter = null)
    {
        $this->filter = $filter;
        $filter->addFilterView($this);
    }

    /**
     * @return Person
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param Person $agent
     */
    public function setAgent(Person $agent = null)
    {
        $this->agent = $agent;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields = array())
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getIconFields()
    {
        return $this->icon_fields;
    }

    /**
     * @param array $icon_fields
     */
    public function setIconFields(array $icon_fields = array())
    {
        $this->icon_fields = $icon_fields;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options = array())
    {
        $this->options = $options;
    }

    public function isPrivate()
    {
        return null !== $this->agent;
    }

    public function addOption($key, $val)
    {
        $this->options[$key] = $val;
    }

    public function removeOption($key)
    {
        if (array_key_exists($key, $this->options)) {
            unset($this->options[$key]);
        }
    }

    public function addIconField($field)
    {
        if (!in_array($field, $this->icon_fields)) {
            $this->icon_fields[] = $field;
        }
    }

    public function removeIconField($field)
    {
        if (in_array($field, $this->icon_fields)) {
            $this->icon_fields = array_values(
                array_filter(
                    $this->icon_fields,
                    function ($val) use ($field) {
                        return $field != $val;
                    }
                )
            );
        }
    }

    public function addField($field)
    {
        if (!in_array($field, $this->fields)) {
            $this->fields[] = $field;
        }
    }

    public function removeField($field)
    {
        if (in_array($field, $this->fields)) {
            $this->fields = array_values(
                array_filter(
                    $this->fields,
                    function ($val) use ($field) {
                        return $field != $val;
                    }
                )
            );
        }
    }
}
