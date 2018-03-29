<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

class NewCommentDownload extends NewCommentAbstract
{
    public function getDetails()
    {
        $details                   = parent::getDetails();
        $details['download_id']    = $this->comment->download['id'];
        $details['download_title'] = $this->comment->download['title'];

        return $details;
    }
}
