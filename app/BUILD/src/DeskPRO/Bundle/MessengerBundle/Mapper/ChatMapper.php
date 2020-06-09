<?php

namespace DeskPRO\Bundle\MessengerBundle\Mapper;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatMessages;
use DeskPRO\Bundle\MessengerBundle\Exception\MessengerApiException;
use DeskPRO\Component\Util\RandUtils;
use Doctrine\ORM\EntityManager;
use Orb\Input\Cleaner\Cleaner;

/**
 * Class ChatMapper.
 */
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

    /**
     * @param array $data
     * @param ChatConversation $chat
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return ChatMessage
     */
    public function createChatMessage($data, ChatConversation $chat)
    {
        $message = $this->getBasicMessage($chat);
        $errors  = [];

        if (isset($data['blobs']) && !is_array($data['blobs'])) {
            $errors['blobs'] = 'Blobs should be an array';
        }

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
            $message->setIsUser(false)->setOrigin(ChatMessage::ORIGIN_AGENT);
        } elseif (isset($data['origin']) && $data['origin'] === ChatMessage::ORIGIN_USER) {
            $message->setIsUser(true)->setOrigin(ChatMessage::ORIGIN_USER);
        } else {
            $errors['origin'] = sprintf(
                'Unexpected value. Only %s and %s are allowed.',
                ChatMessage::ORIGIN_USER,
                ChatMessage::ORIGIN_AGENT
            );
        }

        $message->setMetadata(['uuid' => $this->getUuid($data), 'is_user_message' => $message->getIsUser()]);

        if ($errors) {
            throw new MessengerApiException($errors);
        }

        return $message;
    }

    /**
     * @param array            $data
     * @param ChatConversation $chat
     */
    public function createChatAttachment($data, ChatConversation $chat)
    {
        $message = $this->getBasicMessage($chat);
        $errors  = [];
        if (!$blob = $this->em->find(Blob::class, $data['blob']['id'])) {
            throw new MessengerApiException('Wrong blob id!');
        }
        $blob->setIsTemp(false);

        if (isset($data['blob']) && !is_array($data['blob'])) {
            $errors['blob'] = 'Blob should be an array';
        }

        $content = sprintf('File: <a href="%s" target="_blank">%s</a> (%s)',
            $data['blob']['download_url'],
            $data['blob']['filename'],
            $data['blob']['filesize_readable']
        );

        if ($data['blob']['is_image']) {
            $content = sprintf('%s<div class="file-thumb"><img src="%s?s=50" /></div>', $content, $data['blob']['download_url']);
        }

        $message
            ->setMetadata([
                'uuid'    => $this->getUuid($data),
                'type'    => 'file',
                'blob_id' => $data['blob']['id'],
                'blob'    => [
                    'blob_id'           => $data['blob']['id'],
                    'blob_auth'         => $data['blob']['auth'],
                    'blob_auth_id'      => $data['blob']['auth_id'],
                    'filesize_readable' => $data['blob']['filesize_readable'],
                    'filename'          => $data['blob']['filename'],
                    'download_url'      => $data['blob']['download_url'],
                    'is_image'          => $data['blob']['is_image'],
                ],
            ])
            ->setContent($content)
            ->setIsHtml(true)
            ->setIsUser(true)
            ->setOrigin(ChatMessage::ORIGIN_USER);

        return $message;
    }

    /**
     * @param ChatMessage $message
     *
     * @return array
     */
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
            'meta'         => $this->transformMeta($metadata),
        ];
    }

    private function getUuid($data)
    {
        return isset($data['uuid']) && trim($data['uuid']) ? $data['uuid'] : RandUtils::uuidV4();
    }

    private function getBasicMessage(ChatConversation $chat)
    {
        $message = new ChatMessage();
        // we need to handle this staff with jwt and other things probably
        if ($chat->getPerson()) {
            $message->setAuthor($chat->getPerson());
        }

        return $message;
    }

    /**
     * @param $metadata
     *
     * @return array
     */
    private function transformMeta($metadata)
    {
        if (isset($metadata['type']) && $metadata['type'] === 'file') {
            $res = [
                'type'        => $metadata['type'],
                'downloadUrl' => $metadata['blob']['download_url'],
                'isImage'     => $metadata['blob']['is_image'],
                'filesize'    => $metadata['blob']['filesize_readable'],
            ];
        } elseif (isset($metadata['chat_assigned'])) {
            $res = [
                'type' => 'chat.agentAssigned',
            ];
        } elseif (isset($metadata['chat_unassigned'])) {
            $res = [
                'type' => 'chat.agentUnassigned',
            ];
        } elseif (isset($metadata['user_joined'])) {
            $res = [
                'type' => 'chat.userJoined',
            ];
        } elseif (isset($metadata['user_left'])) {
            $res = [
                'type' => 'chat.userLeft',
            ];
        } else {
            $res = [
                'type' => 'message',
            ];
        }

        return $res;
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

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @throws \Exception
     *
     * @return ChatMessage
     */
    public function createUserTrackMessage($chat, array $request)
    {
        $errors = [];
        if (!isset($request['page_url']) || !trim($request['page_url'])) {
            $errors['page_url'] = 'page_url can\'t be blank';
        }
        if (!isset($request['page_title']) || !trim($request['page_title'])) {
            $errors['page_title'] = 'page_title can\'t be blank';
        }

        if ($errors) {
            throw new MessengerApiException($errors);
        }

        return UserChatMessages::createUserTrackMessage(
            $chat,
            $this->cleanText($request['page_url']),
            $this->cleanText($request['page_title'])
        );
    }

    /**
     * @param $request
     *
     * @return string
     */
    public function createUserViewPageMessage($request)
    {
        $message = '';
        if (isset($request['page_url']) && trim($request['page_url'])) {
            $message .= sprintf('The user was browsing %s', trim($request['page_url']));
        }
        if (isset($request['page_title']) && trim($request['page_title'])) {
            $message .= sprintf('The user was browsing %s', trim($request['page_title']));
        }

        return $this->cleanText($message);
    }
}
