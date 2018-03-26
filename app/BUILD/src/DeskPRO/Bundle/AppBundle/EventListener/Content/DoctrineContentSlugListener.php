<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener\Content;

use Application\DeskPRO\Entity\ContentAbstract;
use DeskPRO\Bundle\AppBundle\Content\ContentSlugManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * Just ensures that the slug of a content object is set correctly (pre persist, and pre update) before flushing.
 */
class DoctrineContentSlugListener implements EventSubscriber
{
    /**
     * @var ContentSlugManager
     */
    private $slug_manager;

    private $new_entities;

    public function __construct(ContentSlugManager $slug_manager)
    {
        $this->slug_manager = $slug_manager;
        $this->new_entities = new ArrayCollection();
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $content = $args->getObject();

        if (!$content instanceof ContentAbstract) {
            return;
        }

        $this->ensureValidSlug($content, $args->getObjectManager());
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $content = $args->getObject();

        if (!$content instanceof ContentAbstract) {
            return;
        }

        $this->ensureValidSlug($content, $args->getObjectManager());

        // updates require a signal to the UOW to recalculate its changeset
        $em  = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $uow->recomputeSingleEntityChangeSet(
            $em->getClassMetadata(get_class($content)),
            $content
        );
    }

    protected function ensureValidSlug(ContentAbstract $content, EntityManager $em)
    {
        if ($new_slug_history = $this->slug_manager->ensureValidSlug($content)) {
            $this->new_entities->add($new_slug_history);
            // we remove it here as we are going to add it post-flush below, no worries :)
            $content->getSlugHistory()->removeElement($new_slug_history);
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->new_entities->count() > 0) {
            $em = $args->getEntityManager();

            foreach ($this->new_entities as $entity) {
                $em->persist($entity);
            }

            $this->new_entities = new ArrayCollection(); // clear this to prevent recursive flushing

            $em->flush();
        }
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
            'postFlush',
        ];
    }
}
