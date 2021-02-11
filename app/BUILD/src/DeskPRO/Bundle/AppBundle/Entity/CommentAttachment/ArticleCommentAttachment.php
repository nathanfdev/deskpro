<?php

namespace DeskPRO\Bundle\AppBundle\Entity\CommentAttachment;

use Application\DeskPRO\Entity\ArticleComment;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ArticleCommentAttachment
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\CommentAttachment\ArticleCommentAttachmentRepository")
 * @ORM\Table(name="article_comment_attachments")
 * @ORM\InheritanceType("NONE")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @category Entities
 */
class ArticleCommentAttachment extends CommentAttachment
{
    /**
     * @var ArticleComment
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\ArticleComment")
     * @ORM\JoinColumn(name="article_comment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $article_comment;

    /**
     * @return ArticleComment
     */
    public function getArticleComment()
    {
        return $this->article_comment;
    }

    /**
     * @param ArticleComment $articleComment
     *
     * @return ArticleCommentAttachment
     */
    public function setArticleComment(ArticleComment $articleComment)
    {
        $this->setModelField('article_comment', $articleComment);

        return $this;
    }
}
