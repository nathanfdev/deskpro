<?php

namespace DeskPRO\Bundle\MessengerBundle\Mapper;

use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\MessengerBundle\Exception\MapperException;
use DeskPRO\Component\Util\RandUtils;
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

    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * ChatMapper constructor.
     *
     * @param EntityManager  $em
     * @param Cleaner        $cleaner
     * @param AvatarResolver $avatarResolver
     */
    public function __construct(EntityManager $em, Cleaner $cleaner, AvatarResolver $avatarResolver)
    {
        $this->em             = $em;
        $this->cleaner        = $cleaner;
        $this->avatarResolver = $avatarResolver;
    }

    public function createChatMessage($data)
    {
        $message = new ChatMessage();

        $errors = [];

        if (isset($data['uuid']) && trim($data['uuid'])) {
            $uuid = $data['uuid'];
        } else {
            $uuid = RandUtils::uuidV4();
        }

        $message->setMetadata(['uuid' => $uuid]);

        if (isset($data['message']) && trim($data['message'])) {
            $message->setContent($this->cleanText($data['message']))->setIsHtml(true);
        } else {
            $errors['message'] = 'Message could not be empty';
        }

        if (isset($data['author'])) {
            if ($author = $this->em->find(Person::class, (int) $data['author'])) {
                $message->setAuthor($author);
            } else {
                $errors['author'] = sprintf('Wrong author id (%d) given. Couldn\'t find author', $data['author']);
            }
        }
        if (isset($data['origin']) && $data['origin'] === ChatMessage::ORIGIN_AGENT) {
            $message->setIsUser(false);
        } elseif (isset($data['origin']) && $data['origin'] === ChatMessage::ORIGIN_USER) {
            $message->setIsUser(true);
        } else {
            $errors['origin'] = sprintf(
                'Unexpected value. Only %s and %s are allowed.',
                ChatMessage::ORIGIN_USER,
                ChatMessage::ORIGIN_AGENT
            );
        }

        if ($message->getAuthor()) {
            $message->setOrigin($message->getAuthor()->isAgent() ? ChatMessage::ORIGIN_AGENT : ChatMessage::ORIGIN_USER);
        } else {
            $message->setOrigin($message->getIsUser() ? ChatMessage::ORIGIN_USER : ChatMessage::ORIGIN_AGENT);
        }

        if ($errors) {
            throw new MapperException($errors);
        }

        return $message;
    }

    public function mapMessageToArray(ChatMessage $message)
    {
        $metadata = $message->getMetadata();

        $uuid = isset($metadata['uuid']) ? $metadata['uuid'] : '';

        return [
            'id'     => $message->getId() ?: 0,
            'chat'   => $message->getConversationId(),
            'name'   => $message->getPersonName(),
            'author' => $message->getAuthorId(),
            'avatar' => $message->getAuthor()
                ? $this->avatarResolver->getAvatar($message->getAuthor())
                : $this->avatarResolver->getDefaultPersonAvatar(),
            'message'      => $message->isHtml() ? $message->getContentHtml() : $message->getContent(),
            'origin'       => $message->getIsSys() ? 'system' : $message->getOrigin(),
            'date_created' => $message->getDateCreated()->format(\DateTime::ISO8601),
            'is_user'      => $message->getIsUser(),
            'is_sys'       => $message->getIsSys(),
            'is_html'      => $message->isHtml(),
            'uuid'         => $uuid,
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
