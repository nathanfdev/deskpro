<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CustomFields;

class CommunityFieldManager extends FieldManager
{
    /**
     * Get the category field if it exists and has options.
     *
     * @return \Application\DeskPRO\Entity\CustomDefCommunityTopic|null
     */
    public function getUserCategoryField()
    {
        $field = $this->getSystemField('chan');
        if (!$field) {
            return;
        }

        if (!$this->getFieldChildren($field)) {
            return;
        }

        return $field;
    }
}
