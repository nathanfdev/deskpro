<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AppBundle\EventListener;

use Application\AppBundle\Service\ContentSlugManager;
use Application\DeskPRO\Entity\ArticleSlugHistory;
use Application\DeskPRO\Entity\ContentAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

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
        $em = $args->getObjectManager();
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
        return array(
            'prePersist',
            'preUpdate',
            'postFlush'
        );
    }
}
