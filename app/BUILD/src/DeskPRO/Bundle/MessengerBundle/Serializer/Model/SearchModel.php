<?php

namespace DeskPRO\Bundle\MessengerBundle\Serializer\Model;

use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\ContentAbstract;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;

/**
 * Class TechInfo.
 */
class SearchModel implements MessengerModelInterface
{
    /**
     * @var ContentAbstract
     */
    private $entity;

    /**
     * @var ObjectRouter
     */
    private $objectRouter;

    /**
     * AgentInfo constructor.
     *
     * @param ContentAbstract $entity
     */
    public function __construct(ContentAbstract $entity, ObjectRouter $objectRouter)
    {
        $this->entity       = $entity;
        $this->objectRouter = $objectRouter;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'      => $this->entity->getId(),
            'title'   => $this->entity->getTitle(),
            'excerpt' => $this->entity->getExcerptHtml(25),
            'type'    => $this->getTypeKey($this->entity),
            'link'    => $this->objectRouter->getPortalUrl($this->entity),
        ];
    }

    private function getTypeKey($r)
    {
        if ($r instanceof Entity\Article) {
            $type = 'article';
        } elseif ($r instanceof Entity\News) {
            $type = 'news';
        } elseif ($r instanceof Entity\Download) {
            $type = 'download';
        } elseif ($r instanceof Entity\CommunityTopic) {
            $type = 'community';
        } elseif ($r instanceof Entity\Topic) {
            $type = 'topic';
        } elseif ($r instanceof Entity\Ticket) {
            $type = 'ticket';
        } elseif ($r instanceof Entity\Person) {
            $type = 'person';
        } elseif ($r instanceof Entity\ChatConversation) {
            $type = 'chat_conversation';
        } else {
            $type = 'unknown';
        }

        return $type;
    }
}
