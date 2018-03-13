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
