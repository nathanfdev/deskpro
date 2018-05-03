<?php

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
