<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

class NewCommentFeedback extends NewCommentAbstract
{
    public function getDetails()
    {
        $details                   = parent::getDetails();
        $details['feedback_id']    = $this->comment->feedback['id'];
        $details['feedback_title'] = $this->comment->feedback['title'];

        return $details;
    }
}
