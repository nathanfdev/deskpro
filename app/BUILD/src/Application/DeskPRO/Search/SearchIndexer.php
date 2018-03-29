<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Topic;
use FOS\ElasticaBundle\Persister\ObjectPersister;

/**
 * When something needs to be indexed, index it through this.
 */
class SearchIndexer
{
    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    public function handle(array $updates, array $deletes)
    {
        //------------------------------
        // Elastic
        //------------------------------

        if ($this->container->getSetting('elastica.enabled')) {
            $updatesByType = [];
            $deletesByType = [];

            $getPersister = function ($object) {
                switch (true) {
                    case $object instanceof Article:
                        return 'fos_elastica.object_persister.deskpro.article';
                    case $object instanceof News:
                        return 'fos_elastica.object_persister.deskpro.news';
                    case $object instanceof Download:
                        return 'fos_elastica.object_persister.deskpro.download';
                    case $object instanceof Feedback:
                        return 'fos_elastica.object_persister.deskpro.feedback';
                    case $object instanceof Ticket:
                        return 'fos_elastica.object_persister.deskpro.ticket';
                    case $object instanceof Person:
                        return 'fos_elastica.object_persister.deskpro.person';
                    case $object instanceof Organization:
                        return 'fos_elastica.object_persister.deskpro.organization';
                    case $object instanceof ChatConversation:
                        return 'fos_elastica.object_persister.deskpro.chat_conversation';
                    case $object instanceof Topic:
                        return 'fos_elastica.object_persister.deskpro.topic';
                }

                return;
            };

            foreach ($updates as $object) {
                $persisterId = $getPersister($object);
                if ($persisterId) {
                    if (!isset($updatesByType[$persisterId])) {
                        $updatesByType[$persisterId] = [];
                    }
                    $updatesByType[$persisterId][] = $object;
                }
            }
            foreach ($deletes as $object) {
                $persisterId = $getPersister($object);
                if ($persisterId) {
                    if (!isset($deletesByType[$persisterId])) {
                        $deletesByType[$persisterId] = [];
                    }
                    $deletesByType[$persisterId][] = $object;
                }
            }

            foreach ($updatesByType as $persisterId => $objects) {
                /** @var ObjectPersister $persister */
                $persister = $this->container->get($persisterId);
                $persister->replaceMany($objects);
            }
            foreach ($deletesByType as $persisterId => $objects) {
                /** @var ObjectPersister $persister */
                $persister = $this->container->get($persisterId);
                $persister->deleteMany($objects);
            }

        //------------------------------
        // Default
        //------------------------------
        }

        foreach ($updates as $object) {
            switch (true) {
                case $object instanceof Article:
                case $object instanceof News:
                case $object instanceof Download:
                case $object instanceof Feedback:
                    App::getContainer()->getSearchAdapter()->updateObjectsInIndex([$object]);
                    break;
            }
        }
        foreach ($deletes as $object) {
            switch (true) {
                case $object instanceof Article:
                case $object instanceof News:
                case $object instanceof Download:
                case $object instanceof Feedback:
                    App::getContainer()->getSearchAdapter()->deleteObjectsFromIndex([$object]);
                    break;
            }
        }
    }
}
