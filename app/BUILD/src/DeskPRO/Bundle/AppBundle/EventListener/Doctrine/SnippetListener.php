<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\Labels\Label;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Entity\SnippetChangeLog;
use DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation;
use DeskPRO\Bundle\AppBundle\Notification\Event\Snippet\SnippetsUpdatedEvent;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LabelListener.
 *
 * Listens for Label entities lifecycle callbacks and performs needed actions on corresponding LabelDef
 */
class SnippetListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ActionAlertsListener constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        /** @var $snippet Snippet */
        if (!($snippet = $args->getEntity()) instanceof Snippet) {
            return;
        }
        $this->onSnippetsUpdate($snippet, 'update');
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        /** @var $snippet Snippet */
        if (($snippet = $args->getEntity()) instanceof Snippet) {
            $this->onSnippetsUpdate($snippet, 'update');
        }
        if (($snippetTranslation = $args->getEntity()) instanceof SnippetTranslation) {
            $this->onSnippetsUpdate($snippetTranslation->getSnippet(), 'update');
        }

        return;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        /** @var $snippet Snippet */
        if (!($snippet = $args->getEntity()) instanceof Snippet) {
            return;
        }
        $this->onSnippetsUpdate($snippet, 'remove');
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em  = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {
            if ($entity instanceof SnippetTranslation) {
                $changeSet = $uow->getEntityChangeSet($entity);
                if (isset($changeSet['content']) || isset($changeSet['type'])) {
                    $type      = isset($changeSet['type']) ? $changeSet['type'][0] : $entity->getType();
                    $content   = isset($changeSet['content']) ? $changeSet['content'][0] : $entity->getContent();
                    $changeLog = $this->saveChanges($entity, $content, $type);
                    // place here all the setters
                    $em->persist($changeLog);
                    $uow->computeChangeSet($em->getClassMetadata(get_class($changeLog)), $changeLog);
                }
            }
        }
    }

    /**
     * @param Snippet $snippet
     * @param $action
     */
    private function onSnippetsUpdate(Snippet $snippet, $action)
    {
        $this->container
            ->get('event_dispatcher')
            ->dispatch(SnippetsUpdatedEvent::EVENT_NAME, new SnippetsUpdatedEvent(
                $snippet,
                $action
            ));
    }

    /**
     * @param SnippetTranslation $snippetTranslation
     * @param string             $oldContent
     * @param $type
     *
     * @return SnippetChangeLog
     */
    private function saveChanges(SnippetTranslation $snippetTranslation, $oldContent, $type)
    {
        $changeLog = new SnippetChangeLog();
        $changeLog->setSnippet($snippetTranslation->getSnippet());
        $changeLog->setLanguage($snippetTranslation->getLanguage());
        $changeLog->setType($type);
        $changeLog->setContent($oldContent);
        $changeLog->setPerson($this->getPerson());

        return $changeLog;
    }

    /**
     * @return Person
     */
    private function getPerson()
    {
        $token = $this->container->get('security.token_storage')->getToken();

        return $token->getUser();
    }
}
