<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Orb\Validator\AbstractValidator;

class NewNewsValidator extends AbstractValidator
{
    /**
     * @param \Application\AgentBundle\Form\Model\NewNews $news
     *
     * @return bool
     */
    protected function checkIsValid($news)
    {
        if (!$news->category_id) {
            $this->addError('category_id.invalid');
        } else {
            $cat = App::getOrm()->find('DeskPRO:NewsCategory', $news->category_id);
            if (!$cat) {
                $this->addError('category_id.invalid');
            }
        }

        if (!$news->title) {
            $this->addError('title.missing');
        }

        if (!$news->status) {
            $this->addError('status.invalid');
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }
}
