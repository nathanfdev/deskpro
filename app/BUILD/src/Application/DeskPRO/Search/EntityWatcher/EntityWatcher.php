<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\EntityWatcher;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\LabelArticle;
use Application\DeskPRO\Entity\LabelDownload;
use Application\DeskPRO\Entity\LabelFeedback;
use Application\DeskPRO\Entity\LabelNews;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\ObjectLang;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonPhoneNumber;
use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use Doctrine\ORM\Event\OnFlushEventArgs;

class EntityWatcher implements \Doctrine\Common\EventSubscriber
{
    /** @var array */
    public static $watched_entities = [
        Article::class               => 1,
        LabelArticle::class          => 1,
        Download::class              => 1,
        LabelDownload::class         => 1,
        Feedback::class              => 1,
        LabelFeedback::class         => 1,
        News::class                  => 1,
        LabelNews::class             => 1,
        Ticket::class                => 1,
        TicketMessage::class         => 1,
        Topic::class                 => 1,
        Person::class                => 1,
        PersonEmail::class           => 1,
        PersonPhoneNumber::class     => 1,
        Organization::class          => 1,
        ChatConversation::class      => 1,
        ChatMessage::class           => 1,
        ObjectLang::class            => 1,
        PersonUsersourceAssoc::class => 1,
    ];

    /**
     * @var \Orb\Filter\FilterInterface[]
     */
    protected $entity_filters = [];

    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected $container;

    /**
     * @var bool
     */
    protected $is_running = false;

    /**
     * @var array
     */
    protected $updates = ['updates' => [], 'deletes' => []];

    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
        \DpShutdown::add([$this, 'flushUpdatesQuiet']);
    }

    /**
     * Flushes updates and eats errors.
     */
    public function flushUpdatesQuiet()
    {
        try {
            $this->flushUpdates();
        } catch (\Exception $e) {
        }
    }

    /**
     * Flushes all updates.
     */
    public function flushUpdates()
    {
        if ($this->is_running) {
            return;
        }
        $this->is_running = true;

        $updates = array_map(function ($v) {
            return $v['ent'];
        }, $this->updates['updates']);
        $deletes = array_map(function ($v) {
            return $v['ent'];
        }, $this->updates['deletes']);

        $this->updates = ['updates' => [], 'deletes' => []];

        $GLOBALS['DP_HAS_UPDATED_SEARCH_TABLES'] = true;

        if ($this->container) {
            /** @var \Application\DeskPRO\Search\SearchIndexer $indexer */
            $indexer = $this->container->getSystemService('search_indexer');
            $indexer->handle($updates, $deletes);
        }

        $this->is_running = false;
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        if ($this->is_running) {
            return;
        }
        $this->is_running = true;

        $update = [];
        $delete = [];

        $em  = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $ent) {
            if (self::isWatchedEntity($ent)) {
                $ent = $this->replaceEntity($ent);
                if ($ent) {
                    $update[] = $ent;
                }
            }
        }
        foreach ($uow->getScheduledEntityUpdates() as $ent) {
            if (self::isWatchedEntity($ent)) {
                $ent = $this->replaceEntity($ent);
                if ($ent) {
                    $update[] = $ent;
                }
            }
        }
        foreach ($uow->getScheduledEntityDeletions() as $ent) {
            if (self::isWatchedEntity($ent)) {
                $class = get_class($ent);
                $class = substr($class, strrpos($class, '\\') + 1);
                $ent   = $this->replaceEntity($ent);
                if ($ent) {
                    if (0 === strpos($class, 'Label')) {
                        $update[] = $ent;
                    } else {
                        $delete[] = $ent;
                    }
                }
            }
        }

        foreach ($update as $ent) {
            if ($ent) {
                $name = self::getEntityClassName($ent);
                $id   = $ent->getId();

                $changeSet = $uow->getEntityChangeSet($ent);
                if ($ent instanceof Ticket && isset($changeSet['status'][1]) && TicketStatus::STATUS_TYPE_HIDDEN == $changeSet['status'][1]) {
                    $action = 'deletes';
                } else {
                    $action = 'updates';
                }

                $this->updates[$action]["$name-$id"] = ['entity' => $name, 'id' => $id, 'ent' => $ent];
            }
        }
        foreach ($delete as $ent) {
            if ($ent) {
                $name                                  = self::getEntityClassName($ent);
                $id                                    = $ent->getId();
                $this->updates['deletes']["$name-$id"] = ['entity' => $name, 'id' => $id, 'ent' => $ent];
            }
        }

        $this->is_running = false;
    }

    /**
     * @param object $ent
     *
     * @return object
     */
    private function replaceEntity($ent)
    {
        if ($ent instanceof LabelArticle) {
            return $ent->article;
        } elseif ($ent instanceof LabelNews) {
            return $ent->news;
        } elseif ($ent instanceof LabelDownload) {
            return $ent->download;
        } elseif ($ent instanceof LabelFeedback) {
            return $ent->feedback;
        } elseif ($ent instanceof TicketMessage) {
            return $ent->ticket;
        } elseif ($ent instanceof PersonEmail) {
            return $ent->person;
        } elseif ($ent instanceof PersonPhoneNumber) {
            return $ent->person;
        } elseif ($ent instanceof ChatMessage) {
            return $ent->conversation;
        } elseif ($ent instanceof ObjectLang) {
            $em      = $this->container->getEm();
            $refId   = $ent->getRefId();
            $refType = $ent->getRefType();

            if (!$refId) {
                return;
            }

            if ($refType === 'articles') {
                return $em->getRepository(Article::class)->find($refId);
            } else {
                return;
            }
        } elseif ($ent instanceof PersonUsersourceAssoc) {
            return $ent->getPerson();
        }

        return $ent;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            \Doctrine\ORM\Events::onFlush,
        ];
    }

    /**
     * Check if an entity is watched.
     *
     * @param $entity
     *
     * @return bool
     */
    public static function isWatchedEntity($entity)
    {
        $name = self::getEntityClassName($entity);

        return isset(self::$watched_entities[$name]);
    }

    /**
     * @param object $entity
     *
     * @return string
     */
    public static function getEntityClassName($entity)
    {
        if (is_string($entity)) {
            return $entity;
        } elseif ($entity instanceof \Doctrine\ORM\Proxy\Proxy) {
            return get_parent_class($entity);
        } else {
            return get_class($entity);
        }
    }
}
