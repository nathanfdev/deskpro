<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class AssetFilterParamConverter implements ParamConverterInterface
{
    /** @var Domain\Filters  */
    private $filters;

    public function __construct(Domain\Filters $filters)
    {
        $this->filters = $filters;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $filterValues = new Domain\FilterValueArrayMap();
        $this->resolveFilterValues($request->attributes, $filterValues);

        $attributeName = $configuration->getName();
        $filter = $this->filters->convertValueMapToAssetFilter($filterValues);
        $request->attributes->set($attributeName, $filter);

        return true;
    }

    private function resolveFilterValues(ParameterBag $params, Domain\FilterValueArrayMap $filterValues)
    {
        if ($params->has('ext')) {
            $filterValues->setFileExtension($params->get('ext'));
        }

        if ($params->has('path')) {
            $filterValues->setFilePathPattern($params->get('path'));
        }
    }

    public function supports(Configuration\ParamConverter $configuration) {
        //TODO check that this class supports the configuration
        return true;
    }
}
