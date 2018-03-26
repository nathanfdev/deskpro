<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

/**
 * Adapters that use data from a form should implement this interface.
 */
interface FormLoginInterface extends AdapterInterface
{
    /**
     * Sets the data got from a form.
     *
     * @param string $url The URL
     */
    public function setFormData(array $form_data);
}
