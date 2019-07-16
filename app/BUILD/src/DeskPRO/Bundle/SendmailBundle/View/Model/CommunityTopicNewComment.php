<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\CommunityTopicComment;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic;
use JMS\Serializer\Annotation as JMS;

class CommunityTopicNewComment extends EmailBaseType
{
    /**
     * The new comment.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\CommunityTopicComment")
     *
     * @var CommunityTopicComment
     */
    protected $comment;

    /**
     * The community topic has been commented.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic")
     *
     * @var CommunityTopic
     */
    protected $topic;

    /**
     * A link to the community topic.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $communityTopicLink;

    protected $templateFile = 'emails_user:community_topic_new_comment.html.twig';

    /**
     * CommunityTopicNewComment constructor.
     *
     * @param CommunityTopicComment $comment
     * @param CommunityTopic        $communityTopic
     * @param                       $communityTopicLink
     */
    public function __construct(CommunityTopicComment $comment, CommunityTopic $communityTopic, $communityTopicLink)
    {
        $this->comment            = $comment;
        $this->topic              = $communityTopic;
        $this->communityTopicLink = $communityTopicLink;
    }
}
