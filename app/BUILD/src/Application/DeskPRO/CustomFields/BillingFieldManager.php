<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CustomFields;

use Orb\Util\Strings;

class BillingFieldManager extends FieldManager
{
    /**
     * Get an array of all defined fields (by doing a query).
     *
     * @return array
     */
    public function getDefinedFields()
    {
        return array_values($this->em->getRepository('DeskPRO:CustomDefBilling')->getTopFields());
    }

    /**
     * @param string $id
     * @param bool   $enabled
     */
    public function setFieldEnabledById($id, $enabled = true)
    {
        if ($custom_field_id = Strings::extractRegexMatch('#^field_(\d+)$#', $id)) {
            $field             = $this->em->find('DeskPRO:CustomDefBilling', $custom_field_id);
            $field->is_enabled = $enabled;

            $this->em->persist($field);
            $this->em->flush($field);
        }
    }
}
