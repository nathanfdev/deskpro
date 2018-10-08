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
            $message->setContent($this->cleanText($data['message']))->setIsHtml(true);
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
        if (isset($data['origin']) && $data['origin'] === ChatMessage::ORIGIN_AGENT) {
            $message->setIsUser(false);
        } elseif (isset($data['origin']) && $data['origin'] === ChatMessage::ORIGIN_USER) {
            $message->setIsUser(true);
        } else {
            throw new \Exception('Wrong origin!');
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
            'id'          => $message->getId() ?: 0,
            'chat'        => $message->getConversationId(),
            'author_name' => $message->getPersonName(),
            'author'      => $message->getAuthorId(),
            'message'     => $message->getContentHtml(),
            'origin'      => $message->getOrigin(),
            'is_user'     => $message->getIsUser(),
            'is_sys'      => $message->getIsSys(),
            'is_html'     => $message->isHtml(),
        ];
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function cleanText($text)
    {
        return $this->cleaner->clean($text, 'html');
    }
}
