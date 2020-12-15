<?php

namespace DeskPRO\Bundle\AppBundle\Entity\CommentAttachment;

use Application\DeskPRO\Entity\NewsComment;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class NewsCommentAttachment
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\CommentAttachment\NewsCommentAttachmentRepository")
 * @ORM\Table(name="news_comment_attachments")
 * @ORM\InheritanceType("NONE")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @category Entities
 */
class NewsCommentAttachment extends CommentAttachment
{
    /**
     * @var NewsComment
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\NewsComment")
     * @ORM\JoinColumn(name="news_comment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $news_comment;

    /**
     * @return NewsComment
     */
    public function getNewsComment(): NewsComment
    {
        return $this->news_comment;
    }

    /**
     * @param NewsComment $newsComment
     *
     * @return NewsCommentAttachment
     */
    public function setNewsComment(NewsComment $newsComment)
    {
        $this->setModelField('news_comment', $newsComment);

        return $this;
    }
}
