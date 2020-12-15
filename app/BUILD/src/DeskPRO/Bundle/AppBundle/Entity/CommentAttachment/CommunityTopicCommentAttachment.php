<?php

namespace DeskPRO\Bundle\AppBundle\Entity\CommentAttachment;

use Application\DeskPRO\Entity\CommunityTopicComment;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CommunityTopicCommentAttachment
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\CommentAttachment\CommunityTopicCommentAttachmentRepository")
 * @ORM\Table(name="communitytopic_comment_attachments")
 * @ORM\InheritanceType("NONE")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @category Entities
 */
class CommunityTopicCommentAttachment extends CommentAttachment
{
    /**
     * @var CommunityTopicComment
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\CommunityTopicComment")
     * @ORM\JoinColumn(name="article_comment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $communitytopic_comment;

    /**
     * @return CommunityTopicComment
     */
    public function getCommunityTopicComment(): CommunityTopicComment
    {
        return $this->communitytopic_comment;
    }

    /**
     * @param CommunityTopicComment $communityTopicComment
     *
     * @return CommunityTopicCommentAttachment
     */
    public function setCommunityTopicComment(CommunityTopicComment $communityTopicComment)
    {
        $this->setModelField('article_comment', $communityTopicComment);

        return $this;
    }
}
