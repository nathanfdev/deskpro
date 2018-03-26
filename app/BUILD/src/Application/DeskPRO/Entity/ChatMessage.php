<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Basic hierarchical category entity. Hierarchy is maintained automatically
 * by a Doctrine NestedSet implementation.
 *
 * @JMS\ExclusionPolicy("all")
 */
class ChatMessage extends DomainObject
{
    const ORIGIN_AGENT = 'agent';
    const ORIGIN_USER  = 'user';

    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $origin = '';

    /**
     * The type of message this is.
     *
     * @var string
     */
    protected $tag = null;

    /**
     * The conversation the message belongs to.
     *
     * @var \Application\DeskPRO\Entity\ChatConversation
     */
    protected $conversation;

    /**
     * Person who created the message.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $author = null;

    /**
     * The authors name at the point of this message.
     *
     * @var string
     */
    protected $person_name = '';

    /**
     * The message.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $content;

    /**
     * Is this a system message? (ended, joined, etc).
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_sys = false;

    /**
     * Is this an user's message? (send from the widget).
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_user = false;

    /**
     * Is the message hidden from the user?
     *
     * @var bool
     */
    protected $is_user_hidden = false;

    /**
     * Is the content an HTML message?
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_html = false;

    /**
     * Additional data.
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $metadata = [];

    /**
     * Date message was created.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Date message was received.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_received = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this['date_created'] = new \DateTime();
    }

    /**
     * The unique message id (legacy).
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("integer")
     * @JMS\SerializedName("message_id")
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ChatConversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * @return Person
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param Person $author
     *
     * @return $this
     */
    public function setAuthor(Person $author = null)
    {
        // Could be a guest, in which case we dont care
        if ($author && $author->id) {
            $this->setModelField('author', $author);
            if ($author && !$this->person_name) {
                $this['person_name'] = $author->getDisplayNameUser();
            }
        }

        return $this;
    }

    /**
     * @param string $person_name
     *
     * @return $this
     */
    public function setPersonName($person_name)
    {
        $this->setModelField('person_name', $person_name);

        return $this;
    }

    /**
     * Author id (legacy).
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("integer")
     *
     * @return int
     */
    public function getAuthorId()
    {
        if ($this->author) {
            return $this->author['id'];
        }

        return 0;
    }

    /**
     * Author type (legacy).
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     *
     * @return int
     */
    public function getAuthorType()
    {
        return $this->getIsSys() ? 'sys' : ($this->getIsUser() ? 'user' : 'agent');
    }

    /**
     * Conversation id (legacy).
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("integer")
     *
     * @return int
     */
    public function getConversationId()
    {
        return $this->getConversation()->getId();
    }

    /**
     * @return int|mixed|string
     */
    public function getAuthorName()
    {
        if ($this->is_sys) {
            return '*';
        } elseif ($this->author) {
            return $this->author['display_name_user'];
        } elseif ($this->conversation['person_name']) {
            return $this->conversation['person_name'];
        }

        return 'User';
    }

    /**
     * Gets the URL to a picture for the person. Note that this will always return
     * a path to an image, even if it's the default.
     *
     * @param int       $size
     * @param null|bool $secure
     *
     * @return null|string
     */
    public function getAuthorPictureUrl($size = 80, $secure = null)
    {
        // Null means detect
        if ($secure === null and App::isWebRequest()) {
            $request = App::getRequest();
            if ($request->isSecure()) {
                $secure = true;
            }
        }

        $url = false;
        if ($this->author) {
            $url = $this->author->getPictureUrl($size, $secure);
        }

        if (!$url && !$this->conversation->is_agent) {
            $url = $this->conversation->getPersonPictureUrl($size, $secure);
        }

        if (!$url) {
            $url = App::get('router')->generate(
                'serve_default_picture',
                [
                    's'        => $size,
                    'size-fit' => 1,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        if ($secure) {
            $url = preg_replace('#^http:#', 'https:', $url);
        }

        return $url;
    }

    /**
     * @param bool $is_html
     *
     * @return $this
     */
    public function setIsHtml($is_html)
    {
        $this->setModelField('is_html', $is_html);

        return $this;
    }

    /**
     * @return bool
     */
    public function isHtml()
    {
        return $this->is_html;
    }

    /**
     * @param bool $is_sys
     *
     * @return $this
     */
    public function setIsSys($is_sys)
    {
        $this->setModelField('is_sys', $is_sys);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSys()
    {
        return $this->is_sys;
    }

    /**
     * @param bool $is_user
     *
     * @return $this
     */
    public function setIsUser($is_user)
    {
        $this->setModelField('is_user', $is_user);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsUser()
    {
        return $this->is_user;
    }

    /**
     * @param $is_user_hidden
     *
     * @return $this
     */
    public function setIsUserHidden($is_user_hidden)
    {
        $this->setModelField('is_user_hidden', $is_user_hidden);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsUserHidden()
    {
        return $this->is_user_hidden;
    }

    /**
     * @param array $metadata
     *
     * @return $this
     */
    public function setMetadata(array $metadata)
    {
        $this->setModelField('metadata', $metadata);

        return $this;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param string $origin
     *
     * @return $this
     */
    public function setOrigin($origin)
    {
        $this->setModelField('origin', $origin);

        return $this;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->setModelField('content', $content);

        return $this;
    }

    public function _setUserName()
    {
        // If we have no name, then assume the message is
        // by the user who started the chat
        if (!$this->person_name && $this->conversation['person_name']) {
            $this['person_name'] = $this->conversation['person_name'];
        }
    }

    /**
     * Get a basic array of message information. These are generally used in templates or with
     * client messages to render the message.
     *
     * @return array
     */
    public function getInfo()
    {
        $info = [];

        $info['conversation_id'] = $this->conversation->id;
        $info['message_id']      = $this->id;

        if ($this->is_sys) {
            $info['author_id']   = 0;
            $info['author_name'] = '*';
            $info['author_type'] = 'sys';
        } elseif ($this->author) {
            $info['author_id']   = $this->author->id;
            $info['author_name'] = $this->author->display_name_user;
            $info['author_type'] = $this->author->is_agent ? 'agent' : 'user';

            // Handle the case where the author is an agent in the user interface
            if ($info['author_type'] == 'agent' && isset($this->metadata['is_user_message'])) {
                $info['author_type'] = 'user';
            }
        } else {
            $info['author_id']   = 0;
            $info['author_name'] = $this->getAuthorName();
            $info['author_type'] = 'user';
        }

        $info['content']      = $this->content;
        $info['is_html']      = $this->is_html;
        $info['metadata']     = $this->metadata;
        $info['date_created'] = $this->date_created->getTimestamp();

        return $info;
    }

    /**
     * Get message as HTML.
     *
     * @return string
     */
    public function getContentHtml()
    {
        if ($this->is_html) {
            return $this->content;
        }

        $content = htmlspecialchars($this->content, \ENT_QUOTES, 'UTF-8');
        $content = nl2br($content);

        return $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated($date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateReceived()
    {
        return $this->date_received;
    }

    /**
     * @param \DateTime $date_received
     *
     * @return ChatMessage
     */
    public function setDateReceived($date_received)
    {
        $this->setModelField('date_received', $date_received);

        return $this;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);
        if (is_string($data['content'])) {
            $content = @json_decode($data['content'], true);
            if ($content) {
                $data['content'] = $content;
            }
        }

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'chat_messages']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->addLifecycleCallback('_setUserName', 'prePersist');
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'tag',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'tag',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'origin',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'origin',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'person_name',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'person_name',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'content',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'content',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_sys',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_sys',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_user',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_user',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_user_hidden',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_user_hidden',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_html',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_html',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'metadata',
                'type'       => 'array',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'metadata',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_received',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_received',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'conversation',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\ChatConversation',
                'mappedBy'     => null,
                'inversedBy'   => 'messages',
                'joinColumns'  => [
                    [
                        'name'                 => 'conversation_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'author',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    [
                        'name'                 => 'author_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
    }
}
