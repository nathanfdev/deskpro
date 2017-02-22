<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use DeskPRO\Bundle\AppStoreBundle\Domain;

class StateFilterParamConverter implements ParamConverterInterface
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
        if ($params->has('scopes')) {
            $filterValues->setStateVariableScope($params->get('scopes'));
        }

        if ($params->has('name')) {
            $filterValues->setStateVariableName($params->get('name'));
        }
    }

    public function supports(Configuration\ParamConverter $configuration)
    {
        //TODO check that this class supports the configuration
        return true;
    }
}
