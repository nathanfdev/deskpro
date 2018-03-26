<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Feedback;

use Application\DeskPRO\Entity\CustomDefFeedback;

class UserCategory
{
    /**
     * @var \Application\DeskPRO\Entity\CustomDefFeedback
     */
    protected $field;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefFeedback|null
     */
    protected $sub_field;

    public function __construct(CustomDefFeedback $field, CustomDefFeedback $sub_field = null)
    {
        $this->field     = $field;
        $this->sub_field = $sub_field;
    }

    /**
     * @return \Application\DeskPRO\Entity\CustomDefFeedback
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return \Application\DeskPRO\Entity\CustomDefFeedback|null
     */
    public function getSubField()
    {
        return $this->sub_field;
    }

    /**
     * Get the category ID.
     *
     * @return mixed
     */
    public function getCategoryId()
    {
        if ($this->sub_field) {
            return $this->sub_field->getId();
        }

        return $this->field->getId();
    }

    /**
     * Get the category title.
     *
     * @param string $sep
     *
     * @return string
     */
    public function getTitle($sep = ' > ')
    {
        $parts   = [];
        $parts[] = $this->field->getTitle();

        if ($this->sub_field) {
            $parts[] = $this->sub_field->getTitle();
        }

        return implode($sep, $parts);
    }

    /**
     * Is there a sub-category?
     *
     * @return bool
     */
    public function hasSub()
    {
        return $this->sub_field !== null;
    }
}
