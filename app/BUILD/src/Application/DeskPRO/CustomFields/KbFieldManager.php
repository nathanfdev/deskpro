<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\Entity\CustomDefArticle;
use Orb\Util\Strings;

class KbFieldManager extends FieldManager
{
    /**
     * Get an array of all defined fields (by doing a query).
     *
     * @return array
     */
    public function getDefinedFields()
    {
        return array_values($this->em->getRepository(CustomDefArticle::class)->getTopFields());
    }

    /**
     * @param string $id
     * @param bool   $enabled
     */
    public function setFieldEnabledById($id, $enabled = true)
    {
        if ($custom_field_id = Strings::extractRegexMatch('#^field_(\d+)$#', $id)) {
            $field             = $this->em->find(CustomDefArticle::class, $custom_field_id);
            $field->is_enabled = $enabled;

            $this->em->persist($field);
            $this->em->flush();
        }
    }
}
