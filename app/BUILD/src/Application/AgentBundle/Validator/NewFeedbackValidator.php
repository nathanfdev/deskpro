<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Orb\Validator\AbstractValidator;

class NewFeedbackValidator extends AbstractValidator
{
    /**
     * @param \Application\AgentBundle\Form\Model\NewFeedback $feedback
     *
     * @return bool
     */
    protected function checkIsValid($feedback)
    {
        if (!$feedback->category_id) {
            $this->addError('category_id.invalid');
        }

        $cat = App::getOrm()->find('DeskPRO:FeedbackCategory', $feedback->category_id);
        if (!$cat) {
            $this->addError('category_id.invalid');
        }

        if (!$feedback->title) {
            $this->addError('title.missing');
        }

        if (!$feedback->status_code) {
            $this->addError('status.invalid');
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }
}
