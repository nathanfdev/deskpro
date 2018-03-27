<?php

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
