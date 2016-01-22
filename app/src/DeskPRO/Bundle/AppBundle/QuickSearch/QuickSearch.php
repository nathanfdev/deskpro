<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\QuickSearch\Adapter\Doctrine;
use DeskPRO\Bundle\AppBundle\QuickSearch\Adapter\ElasticSearch;
use DeskPRO\Kernel\KernelErrorHandler;

/**
 * Class QuickSearch.
 */
class QuickSearch
{
    /**
     * @var Doctrine
     */
    private $doctrine_adapter;

    /**
     * @var ElasticSearch
     */
    private $elastic_search_adapter;

    /**
     * @var SettingsResolver
     */
    private $settings_resolver;

    /**
     * Constructor.
     *
     * @param Doctrine         $doctrine_adapter
     * @param ElasticSearch    $elastic_search_adapter
     * @param SettingsResolver $settings_resolver
     */
    public function __construct(Doctrine $doctrine_adapter, ElasticSearch $elastic_search_adapter, SettingsResolver $settings_resolver)
    {
        $this->doctrine_adapter       = $doctrine_adapter;
        $this->elastic_search_adapter = $elastic_search_adapter;
        $this->settings_resolver      = $settings_resolver;
    }

    /**
     * @param QuickSearchRequest $request
     *
     * @return array
     */
    public function search(QuickSearchRequest $request)
    {
        if (!$request->getQuery()) {
            return [];
        }

        if ($this->settings_resolver->getGlobalSettings()->get('elastica.enabled')) {
            try {
                return $this->elastic_search_adapter->search($request);
            } catch (\Exception $e) {
                KernelErrorHandler::logException($e);

                // fallback on DB search
                return $this->doctrine_adapter->search($request);
            }
        } else {
            return $this->doctrine_adapter->search($request);
        }
    }
}
