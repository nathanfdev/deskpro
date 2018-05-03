<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionChecker;

class PublishChecker extends AbstractChecker
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @param mixed $content
     *
     * @return bool
     */
    public function canDelete($content)
    {
        return $this->person->hasPerm('agent_publish.delete');
    }

    /**
     * @param mixed $content
     *
     * @return bool
     */
    public function canEdit($content)
    {
        if ($this->person->hasPerm('agent_publish.edit')) {
            return true;
        }

        if ($content->person && $content->person->getId() == $this->person->getId()) {
            return true;
        }

        return false;
    }

    /**
     * @param $content
     */
    public function canValidate($content)
    {
        return $this->person->hasPerm('agent_publish.validate');
    }
}
