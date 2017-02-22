<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

use DeskPRO\Bundle\AppBundle\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM;

class AppInstanceParamConverter implements ParamConverterInterface
{
    /** @var ORM\EntityManager */
    private $entityManager;

    /** @var IdentifierParser */
    private $identifierParser;

    public function __construct(ORM\EntityManager $entityManager, IdentifierParser $identifierParser)
    {
        $this->entityManager = $entityManager;
        $this->identifierParser = $identifierParser;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $attributeName = $configuration->getName();
        $request->attributes->set($attributeName, new Entity\AppStore\AppInstance());

        return true;
    }

    public function supports(Configuration\ParamConverter $configuration)
    {
        //TODO check that this class supports the configuration
        return true;
    }
}
