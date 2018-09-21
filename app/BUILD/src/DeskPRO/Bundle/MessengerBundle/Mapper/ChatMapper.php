<?php

namespace DeskPRO\Bundle\MessengerBundle\Mapper;

use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Orb\Input\Cleaner\Cleaner;

class ChatMapper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Cleaner
     */
    private $cleaner;

    public function __construct(EntityManager $em, Cleaner $cleaner)
    {
        $this->em      = $em;
        $this->cleaner = $cleaner;
    }

    public function createChatMessage($data)
    {
        $message = new ChatMessage();

        if (isset($data['message'])) {
            $message->setContent($this->cleaner->clean($data['message'], 'html'))->setIsHtml(true);
        }
        if (isset($data['author'])) {
            if ($author = $this->em->find(Person::class, (int) $data['author'])) {
                $message->setAuthor($author);
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Wrong author id (%d) given. Couldn\'t find author', $data['author']), 400
                );
            }
        } elseif ($data['author_name']) {
            $message->setPersonName($data['author_name']);
        }
        if (isset($data['is_user']) && $data['is_user'] === true) {
            $message->setIsUser(true);
        }

        if ($message->getAuthor()) {
            $message->setOrigin($message->getAuthor()->isAgent() ? ChatMessage::ORIGIN_AGENT : ChatMessage::ORIGIN_USER);
        } else {
            $message->setOrigin($message->getIsUser() ? ChatMessage::ORIGIN_USER : ChatMessage::ORIGIN_AGENT);
        }

        return $message;
    }

    public function mapMessageToArray(ChatMessage $message)
    {
        return [
            'id'          => $message->getId(),
            'chat'        => $message->getConversationId(),
            'author_name' => $message->getPersonName(),
            'author'      => $message->getAuthorId(),
            'message'     => $message->getContentHtml(),
            'is_user'     => $message->getIsUser(),
            'is_sys'      => $message->getIsSys(),
            'is_html'     => $message->isHtml(),
        ];
    }
}
