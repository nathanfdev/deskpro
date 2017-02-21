<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

use DeskPRO\Bundle\AppBundle\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class AppInstanceParamConverter implements ParamConverterInterface
{

    public function apply(Request $request, ParamConverter $configuration)
    {
        $attributeName = $configuration->getName();
        $request->attributes->set($attributeName, new Entity\AppStore\AppInstance());

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return true;
    }
}
