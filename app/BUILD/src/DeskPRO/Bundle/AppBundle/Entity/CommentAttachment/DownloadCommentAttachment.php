<?php

namespace DeskPRO\Bundle\AppBundle\Entity\CommentAttachment;

use Application\DeskPRO\Entity\DownloadComment;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class DownloadCommentAttachment
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\CommentAttachment\DownloadCommentAttachmentRepository")
 * @ORM\Table(name="download_comment_attachments")
 * @ORM\InheritanceType("NONE")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @category Entities
 */
class DownloadCommentAttachment extends CommentAttachmentAbstract
{
    /**
     * @var DownloadComment
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\DownloadComment")
     * @ORM\JoinColumn(name="download_comment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $download_comment;

    /**
     * @return DownloadComment
     */
    public function getDownloadComment(): DownloadComment
    {
        return $this->download_comment;
    }

    /**
     * @param DownloadComment $downloadComment
     *
     * @return DownloadCommentAttachment
     */
    public function setDownloadComment(DownloadComment $downloadComment)
    {
        $this->setModelField('download_comment', $downloadComment);

        return $this;
    }
}
