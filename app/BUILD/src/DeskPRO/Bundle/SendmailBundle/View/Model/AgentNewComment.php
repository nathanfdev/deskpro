<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\CommentAbstract;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentAbstract;
use JMS\Serializer\Annotation as JMS;

class AgentNewComment extends EmailBaseType
{
    /**
     * The new comment.
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
    protected $object;

    /**
     * The type to the content.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $objectType;

    /**
     * Link to agent interface.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $loginLink;

    protected $templateFile = 'emails_agent:new_comment.html.twig';

    /**
     * AgentNewComment constructor.
     *
     * @param CommentAbstract $comment
     * @param ContentAbstract $object
     * @param string          $objectType
     * @param string          $loginLink
     */
    public function __construct(CommentAbstract $comment, ContentAbstract $object, $objectType, $loginLink)
    {
        $this->comment    = $comment;
        $this->object     = $object;
        $this->objectType = $objectType;
        $this->loginLink  = $loginLink;
    }
}
