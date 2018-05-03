<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\Person;

abstract class NewCommentAbstract extends ActionTypeAbstract
{
    /** @var \Application\DeskPRO\Entity\CommentAbstract */
    protected $comment;

    /**
     * @param \Application\DeskPRO\Entity\Person          $person
     * @param \Application\DeskPRO\Entity\CommentAbstract $comment
     */
    public function __construct(Person $person, CommentAbstract $comment)
    {
        $this->person  = $person;
        $this->comment = $comment;
    }

    /**
     * Get a plain array of details that'll be stored in the databaes.
     *
     * @return array
     */
    public function getDetails()
    {
        return [
            'comment_id' => $this->comment['id'],
            'comment'    => $this->comment['content'],
        ];
    }
}
