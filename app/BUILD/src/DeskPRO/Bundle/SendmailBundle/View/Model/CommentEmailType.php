<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\CommentAbstract;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentAbstract;
use JMS\Serializer\Annotation as JMS;

abstract class CommentEmailType extends EmailBaseType
{
    /**
     * The approved comment.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\CommentAbstract")
     *
     * @var CommentAbstract
     */
    protected $comment;

    /**
     * The commented content.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentAbstract")
     *
     * @var ContentAbstract
     */
    protected $content;

    /**
     * The link to the content.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $contentLink;

    /**
     * CommentApproved constructor.
     *
     * @param CommentAbstract $comment
     * @param ContentAbstract $content
     * @param string          $contentLink
     */
    public function __construct(CommentAbstract $comment, ContentAbstract $content, $contentLink)
    {
        $this->comment     = $comment;
        $this->content     = $content;
        $this->contentLink = $contentLink;
    }
}
