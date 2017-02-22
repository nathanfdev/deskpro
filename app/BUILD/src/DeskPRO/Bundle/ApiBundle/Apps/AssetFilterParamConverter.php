<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM;

class AssetFilterParamConverter implements ParamConverterInterface
{
    /** @var ORM\EntityManager */
    private $entityManager;

    public function __construct(ORM\EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $attributeName = $configuration->getName();
        $request->attributes->set($attributeName, []);

        return true;
    }

    public function supports(Configuration\ParamConverter $configuration) {
        //TODO check that this class supports the configuration
        return true;
    }
}
