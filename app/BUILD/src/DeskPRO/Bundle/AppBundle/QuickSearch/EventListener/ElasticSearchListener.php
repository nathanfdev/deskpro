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

namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use DpSys\LowError\SystemErrorHandler;
use FOS\ElasticaBundle\Manager\RepositoryManager;
use FOS\ElasticaBundle\Repository;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ElasticaSearchListener.
 */
class ElasticSearchListener implements EventSubscriberInterface
{
    /**
     * @var RepositoryManager
     */
    private $elasticManager;

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param RepositoryManager $elasticManager
     * @param SettingsResolver  $settingsResolver
     */
    public function __construct(RepositoryManager $elasticManager, SettingsResolver $settingsResolver)
    {
        $this->elasticManager   = $elasticManager;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuickSearchEvents::SEARCH => 'onSearch',
        ];
    }

    /**
     * @param QuickSearchEvent $event
     * @param string           $eventName
     * @param EventDispatcher  $dispatcher
     */
    public function onSearch(QuickSearchEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $context = $event->getContext();
        $request = $event->getRequest();

        $query = implode(' ', $request->getWords());
        if (!$query) {
            return;
        }

        // note: IMHO the better approach is to subscribe both ES and DB to the same event using priority
        // the DB search may be skipped according to event payload
        // ES listener should not be subscribed if ES setting is disabled
        if (!$this->settingsResolver->getGlobalSettings()->get('elastica.enabled')) {
            $dispatcher->dispatch(QuickSearchEvents::SEARCH_FALLBACK, $event);

            return;
        }

        try {
            /** @var Repository $repository */
            $repository = $this->elasticManager->getRepository($context->getEntityName());
            if (method_exists($repository, 'setPersonContext')) {
                $repository->setPersonContext($request->getPerson());
            }

            $sort = 'score';
            if (in_array($request->getSort(), ['score', 'date_active', 'date_created'])) {
                $sort = $request->getSort();
            }

            $options = array_merge($context->getCriteriaOptions(), $request->getParams(), ['sort_type' => $sort]);
            $result  = $repository->find($query, null, $options);
            foreach ($result as $entity) {
                $context->addEntity($entity);
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
            $dispatcher->dispatch(QuickSearchEvents::SEARCH_FALLBACK, $event);
        }
    }
}
