<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

class NewCommentArticle extends NewCommentAbstract
{
    public function getDetails()
    {
        $details                  = parent::getDetails();
        $details['article_id']    = $this->comment->article['id'];
        $details['article_title'] = $this->comment->article['title'];

        return $details;
    }
}
