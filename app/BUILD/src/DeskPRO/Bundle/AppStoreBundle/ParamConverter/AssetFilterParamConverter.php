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

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class AssetFilterParamConverter implements ParamConverterInterface
{
    /** @var Domain\SearchFilters */
    private $searchFilterConvertor;

    public function __construct(Domain\SearchFilters $filters)
    {
        $this->searchFilterConvertor = $filters;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $filterValues = $this->mapParameterBagToFilterValues(
            $request->attributes,
            new Domain\SearchFilterValueArrayMap()
        );

        $filter = $this->searchFilterConvertor->convertValueMapToAssetFilter($filterValues);

        $attributeName = $configuration->getName();
        $request->attributes->set($attributeName, $filter);

        return true;
    }

    private function mapParameterBagToFilterValues(ParameterBag $params, Domain\SearchFilterValueArrayMap $filterValues)
    {
        if ($params->has('ext')) {
            $filterValues->setFileExtension($params->get('ext'));
        }

        if ($params->has('path')) {
            $filterValues->setFilePathPattern($params->get('path'));
        }

        return $filterValues;
    }

    public function supports(Configuration\ParamConverter $configuration)
    {
        //TODO check that this class supports the configuration
        // todo temp excluded tag request until proper statement is added
        return $configuration->getClass() !== TagRequest::class;
    }
}
