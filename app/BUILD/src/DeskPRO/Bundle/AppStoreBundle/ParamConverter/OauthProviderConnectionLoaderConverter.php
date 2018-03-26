<?php

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\OauthProviderConnectionLoader;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class OauthProviderConnectionLoaderConverter implements ParamConverterInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $attributeName = $configuration->getName();
        $from          = $request->attributes->get($attributeName);

        if (empty($from) || !is_string($from)) {
            return false;
        }

        $loader = new OauthProviderConnectionLoader($from, $this->entityManager);
        $request->attributes->set($attributeName, $loader);

        return true;
    }

    public function supports(Configuration\ParamConverter $configuration)
    {
        //TODO check that this class supports the configuration
        // todo temp excluded tag request until proper statement is added
        return $configuration->getClass() !== TagRequest::class;
    }
}
