<?php

namespace DeskPRO\Bundle\AppBundle\QuickSearch;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Topic;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class QuickSearchContext.
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class QuickSearchContext
{
    const TYPE_ARTICLE           = 'article';
    const TYPE_DOWNLOAD          = 'download';
    const TYPE_FEEDBACK          = 'feedback';
    const TYPE_NEWS              = 'news';
    const TYPE_TICKET            = 'ticket';
    const TYPE_TOPIC             = 'topic';
    const TYPE_PERSON            = 'person';
    const TYPE_AGENT             = 'agent';
    const TYPE_ORGANIZATION      = 'organization';
    const TYPE_CHAT_CONVERSATION = 'chat_conversation';

    /**
     * @var string
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $type;

    /**
     * @var QuickSearchResponse
     */
    private $response;

    /**
     * @var ArrayCollection
     */
    private $ids;

    /**
     * @var ArrayCollection
     *
     * @JMS\Expose()
     * @JMS\SerializedName("results")
     * @JMS\Type("array")
     */
    private $entities;

    /**
     * @var ArrayCollection
     */
    private $related;

    /**
     * Constructor.
     *
     * @param string              $type
     * @param QuickSearchResponse $response
     */
    public function __construct($type, QuickSearchResponse $response)
    {
        $this->type     = $type;
        $this->ids      = new ArrayCollection();
        $this->entities = new ArrayCollection();
        $this->related  = new ArrayCollection();
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        $mappingName = self::getDoctrineMapping()[$this->getType()];

        return is_array($mappingName) ? $mappingName[0] : $mappingName;
    }

    /**
     * @return array
     */
    public function getCriteriaOptions()
    {
        $mappingName = self::getDoctrineMapping()[$this->getType()];

        return is_array($mappingName) ? $mappingName[1] : [];
    }

    /**
     * @return QuickSearchResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return bool
     */
    public function isTicket()
    {
        return $this->getEntityName() === Ticket::class;
    }

    /**
     * @return bool
     */
    public function isPerson()
    {
        return $this->getEntityName() === Person::class;
    }

    /**
     * @return bool
     */
    public function isOrganization()
    {
        return $this->getEntityName() === Organization::class;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function addId($id)
    {
        $id = (int) $id;
        if ($id && !$this->ids->contains($id)) {
            $this->ids->add($id);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getDeferredIds()
    {
        $all_ids    = $this->ids->toArray();
        $loaded_ids = $this->entities->map(function ($entity) {
            return $entity->getId();
        })->toArray();

        return array_diff($all_ids, $loaded_ids);
    }

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids->toArray();
    }

    /**
     * @param array $ids
     *
     * @return $this
     */
    public function setIds(array $ids)
    {
        $this->ids = new ArrayCollection($ids);

        return $this;
    }

    /**
     * @param mixed $entity
     *
     * @return $this
     */
    public function addRelatedEntity($entity)
    {
        if (!$this->entities->contains($entity)) {
            $this->related->add($entity);
            $this->addEntity($entity);
        }

        return $this;
    }

    /**
     * @param mixed $entity
     *
     * @return bool
     */
    public function isRelatedEntity($entity)
    {
        return $this->related->contains($entity);
    }

    /**
     * @param mixed $entity
     *
     * @return $this
     */
    public function addEntity($entity)
    {
        if (!$this->entities->contains($entity)) {
            $this->entities->add($entity);
            $this->addId($entity->getId());
        }

        return $this;
    }

    /**
     * @param mixed $entity
     *
     * @return $this
     */
    public function removeEntity($entity)
    {
        if ($this->entities->contains($entity)) {
            $this->entities->removeElement($entity);
            $this->related->removeElement($entity);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->entities->toArray();
    }

    /**
     * @param array $entities
     *
     * @return $this
     */
    public function setEntities(array $entities)
    {
        $this->entities = new ArrayCollection($entities);

        return $this;
    }

    /**
     * @return array
     */
    public static function getDoctrineMapping()
    {
        return [
            self::TYPE_ARTICLE           => Article::class,
            self::TYPE_DOWNLOAD          => Download::class,
            self::TYPE_FEEDBACK          => Feedback::class,
            self::TYPE_NEWS              => News::class,
            self::TYPE_TICKET            => Ticket::class,
            self::TYPE_PERSON            => Person::class,
            self::TYPE_AGENT             => [Person::class, ['is_agent' => true]],
            self::TYPE_ORGANIZATION      => Organization::class,
            self::TYPE_CHAT_CONVERSATION => ChatConversation::class,
            self::TYPE_TOPIC             => Topic::class,
        ];
    }
}
