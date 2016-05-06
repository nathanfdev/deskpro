<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\QuickSearch;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class QuickSearchContext.
 */
class QuickSearchContext
{
    const TYPE_ARTICLE           = 'article';
    const TYPE_DOWNLOAD          = 'download';
    const TYPE_FEEDBACK          = 'feedback';
    const TYPE_NEWS              = 'news';
    const TYPE_TICKET            = 'ticket';
    const TYPE_PERSON            = 'person';
    const TYPE_ORGANIZATION      = 'organization';
    const TYPE_CHAT_CONVERSATION = 'chat_conversation';

    /**
     * @var string
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
        return self::getDoctrineMapping()[$this->getType()];
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
        return $this->getType() === self::TYPE_TICKET;
    }

    /**
     * @return bool
     */
    public function isPerson()
    {
        return $this->getType() === self::TYPE_PERSON;
    }

    /**
     * @return bool
     */
    public function isOrganization()
    {
        return $this->getType() === self::TYPE_ORGANIZATION;
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
        $loaded_ids = $this->entities->map(function ($entity) { return $entity->getId(); })->toArray();

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
            self::TYPE_ARTICLE           => 'DeskPRO:Article',
            self::TYPE_DOWNLOAD          => 'DeskPRO:Download',
            self::TYPE_FEEDBACK          => 'DeskPRO:Feedback',
            self::TYPE_NEWS              => 'DeskPRO:News',
            self::TYPE_TICKET            => 'DeskPRO:Ticket',
            self::TYPE_PERSON            => 'DeskPRO:Person',
            self::TYPE_ORGANIZATION      => 'DeskPRO:Organization',
            self::TYPE_CHAT_CONVERSATION => 'DeskPRO:ChatConversation',
        ];
    }
}
