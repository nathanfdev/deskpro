<?php

namespace DeskPRO\Bundle\AppBundle\Entity\CommentAttachment;

use Application\DeskPRO\Entity\TopicComment;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TopicCommentAttachment
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\CommentAttachment\TopicCommentAttachmentRepository")
 * @ORM\Table(name="topic_comment_attachments")
 * @ORM\InheritanceType("NONE")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @category Entities
 */
class TopicCommentAttachment extends CommentAttachmentAbstract
{
    /**
     * @var TopicComment
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\TopicComment")
     * @ORM\JoinColumn(name="topic_comment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $topic_comment;

    /**
     * @return TopicComment
     */
    public function getTopicComment(): TopicComment
    {
        return $this->topic_comment;
    }

    /**
     * @param TopicComment $topicComment
     *
     * @return TopicCommentAttachment
     */
    public function setTopicComment(TopicComment $topicComment)
    {
        $this->setModelField('topic_comment', $topicComment);

        return $this;
    }
}
