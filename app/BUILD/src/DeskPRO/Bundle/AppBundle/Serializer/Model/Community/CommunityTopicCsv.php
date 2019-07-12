<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Community;

use Application\DeskPRO\Entity\CommunityTopic as CommunityTopicEntity;
use JMS\Serializer\Annotation as JMS;

class CommunityTopicCsv extends CommunityTopic
{
    /**
     * Author's name.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $person;

    /**
     * Category the feedback belongs to.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $statusCategory = null;

    /**
     * Channel the topic belongs to.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $channel;

    /**
     * The main content for the item.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $content = '';

    /**
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\CommunityTopic $communityTopic
     */
    public function __construct(CommunityTopicEntity $communityTopic)
    {
        parent::__construct($communityTopic);

        $this->person         = $communityTopic->getByLine();
        $this->statusCategory = $communityTopic->getStatusCategory() ? $communityTopic->getStatusCategory()->getTitle() : '';
        $this->channel        = $communityTopic->getCategory() ? $communityTopic->getCategory()->getTitle() : '';
        $this->content        = mb_substr($communityTopic->getContentPlain(), 0, 120);
    }
}
