<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class AssetFilterParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration)
    {
        $attributeName = $configuration->getName();
        $request->attributes->set($attributeName, []);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return true;
    }
}
