<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\SavedForm;

use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use Orb\Util\Arrays;

class SavedFormView
{
    /**
     * @var SavedForm
     */
    private $saved_form;

    public function __construct(SavedForm $saved_form)
    {
        $this->saved_form = $saved_form;
    }

    public function getRouteName()
    {
        $meta = $this->saved_form->getMetaData();

        return $meta['route'];
    }

    public function getRouteParams()
    {
        $meta = $this->saved_form->getMetaData();

        $params = $meta['route_params'];

        if (!is_array($params)) {
            $params = [];
        }

        return $params;
    }

    public function getMethod()
    {
        return 'POST';
    }

    public function getFields()
    {
        $new = [];

        $sep       = '.|.';
        $flattened = Arrays::flattenWithKeys($this->saved_form->getFormData(), $sep);
        foreach ($flattened as $flattened_key => $val) {
            $key_parts = explode($sep, $flattened_key);
            $new_key   = '';
            for ($i = 0; $i < count($key_parts); ++$i) {
                if ($i > 0) {
                    $new_key .= '[';
                }
                $new_key .= $key_parts[$i];
                if ($i > 0) {
                    $new_key .= ']';
                }
            }

            // double submit token is not returned. the $key is neccessary, but the value must be
            // an empty string so we can detect when the token is generated on the client before auto-submitting.
            if ($key_parts[(count($key_parts) - 1)] === '_dp_csrf_token') {
                $val = '';
            }

            $new[$new_key] = $val;
        }

        return $new;
    }
}
