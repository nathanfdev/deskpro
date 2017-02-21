<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

use DeskPRO\Bundle\AppBundle\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class AppStateParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration)
    {
        $attributeName = $configuration->getName();
        $request->attributes->set($attributeName, new Entity\AppStore\AppState());

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return true;
    }
}
