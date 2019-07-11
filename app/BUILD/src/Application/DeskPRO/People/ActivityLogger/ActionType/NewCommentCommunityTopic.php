<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

class NewCommentCommunityTopic extends NewCommentAbstract
{
    public function getDetails()
    {
        $details                   = parent::getDetails();
        $details['feedback_id']    = $this->comment->topic['id'];
        $details['feedback_title'] = $this->comment->topic['title'];

        return $details;
    }
}
