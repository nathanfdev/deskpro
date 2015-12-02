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
namespace DeskPRO\Bundle\AppBundle\DataSerializer;

/**
 * A stateful cache we use to store the transformations so we only ever execute a data transformer once
 * per type/id pair (per view).
 */
class DataTransformerRegistry
{
    private $transformed_data;

    public function __construct()
    {
        $this->transformed_data = [];
    }

    public function registerTransformed($type, $id, $transformed, $view = DataTransformerRequest::DEFAULT_VIEW)
    {
        if (!array_key_exists($view, $this->transformed_data)) {
            $this->transformed_data[$view] = [];
        }

        if (!array_key_exists($type, $this->transformed_data[$view])) {
            $this->transformed_data[$view][$type] = [];
        }

        $this->transformed_data[$view][$type][$id] = $transformed;
    }

    public function getTransformed($type, $id, $view = DataTransformerRequest::DEFAULT_VIEW)
    {
        if (!array_key_exists($view, $this->transformed_data)) {
            return;
        }

        if (!array_key_exists($type, $this->transformed_data[$view])) {
            return;
        }

        if (!array_key_exists($id, $this->transformed_data[$view][$type])) {
            return;
        }

        return $this->transformed_data[$view][$type][$id];
    }
}
