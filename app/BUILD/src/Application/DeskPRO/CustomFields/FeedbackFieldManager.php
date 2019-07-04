<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CustomFields;

class FeedbackFieldManager extends FieldManager
{
    /**
     * Get the category field if it exists and has options.
     *
     * @return \Application\DeskPRO\Entity\CustomDefCommunityTopic|null
     */
    public function getUserCategoryField()
    {
        $field = $this->getSystemField('cat');
        if (!$field) {
            return;
        }

        if (!$this->getFieldChildren($field)) {
            return;
        }

        return $field;
    }
}
