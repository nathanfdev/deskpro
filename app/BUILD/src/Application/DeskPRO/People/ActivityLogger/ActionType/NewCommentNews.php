<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

class NewCommentNews extends NewCommentAbstract
{
    public function getDetails()
    {
        $details               = parent::getDetails();
        $details['news_id']    = $this->comment->news['id'];
        $details['news_title'] = $this->comment->news['title'];

        return $details;
    }
}
