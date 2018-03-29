<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Guide;
use Orb\Validator\AbstractValidator;

class NewTopicValidator extends AbstractValidator
{
    /**
     * @param \Application\AgentBundle\Form\Model\NewTopic $topic
     *
     * @return bool
     */
    protected function checkIsValid($topic)
    {
        if (!$topic->guide_id) {
            $this->addError('guide_id.invalid');
        } else {
            $cat = App::getOrm()->find(Guide::class, $topic->guide_id);
            if (!$cat) {
                $this->addError('guide_id.invalid');
            }
        }

        if (!$topic->title) {
            $this->addError('title.missing');
        }

        if (!$topic->status) {
            $this->addError('status.invalid');
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }
}
