<?php

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain\ApplicationStateId;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AppStateParamConverter implements ParamConverterInterface
{
    /** @var Infrastructure\ApplicationStateDoctrineFinder */
    private $finder;

    /** @var Infrastructure\IdentifierParser */
    private $identifierParser;

    public function __construct(Infrastructure\ApplicationStateDoctrineFinder $finder, Infrastructure\IdentifierParser $identifierParser)
    {
        $this->finder = $finder;
        $this->identifierParser = $identifierParser;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $appId = $request->attributes->get('application');
        $stateName = $request->attributes->get('name');

        $stateKey = new Domain\ApplicationStateId($appId, $stateName);
        $entity = $this->finder->find($stateKey);

        if (is_null($entity)) {
            throw new NotFoundHttpException('could not find state');
        }

        $attributeName = $configuration->getName();
        $request->attributes->set($attributeName, $entity);

        return true;
    }

    public function supports(Configuration\ParamConverter $configuration)
    {
        //TODO check that this class supports the configuration
        return true;
    }
}
