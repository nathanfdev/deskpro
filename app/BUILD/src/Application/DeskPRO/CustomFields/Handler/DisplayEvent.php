<?php

namespace Application\DeskPRO\CustomFields\Handler;

use Application\DeskPRO\EventDispatcher\DataEvent;
use Application\DeskPRO\EventDispatcher\FilterPluginInterface;

class DisplayEvent extends DataEvent implements FilterPluginInterface
{
    /** @var array */
    protected $field_def;

    /**
     * {@inheritdoc}
     */
    public function __construct($field_def, $data = [])
    {
        parent::__construct($data);
        $this->field_def = $field_def;
    }

    public function getField()
    {
        return $this->field_def;
    }

    /**
     * {@inheritdoc}
     */
    public function filterPlugins($plugin)
    {
        if (isset($plugin['event_options']['field_table'])) {
            if ($plugin['event_options']['field_table'] != $this->field_def->getTableName()) {
                return false;
            }
        }

        if (isset($plugin['event_options']['field_id'])) {
            if ($plugin['event_options']['field_id'] != $this->field_def['id']) {
                return false;
            }
        }

        return true;
    }
}
