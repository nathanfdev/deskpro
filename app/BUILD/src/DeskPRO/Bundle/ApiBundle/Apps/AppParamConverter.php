<?php

namespace DeskPRO\Bundle\ApiBundle\Apps;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class AppParamConverter implements ParamConverterInterface
{
    /** @var Infrastructure\DoctrineApplicationFinder  */
    private $finder;

    /** @var IdentifierParser */
    private $identifierParser;

    public function __construct(Infrastructure\DoctrineApplicationFinder $finder, IdentifierParser $identifierParser)
    {
        $this->finder = $finder;
        $this->identifierParser = $identifierParser;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $attributeName = $configuration->getName();
        $from = $request->attributes->get($attributeName);

        if (empty($from)) {
            return false;
        }

        $application = $this->convert($from);
        if (empty($application)) {
            return false;
        }

        $request->attributes->set($attributeName, $application);
        return true;
    }

    /**
     * @param string $from
     * @return Entity\AppStore\App
     */
    private function convert($from)
    {
        if ($this->identifierParser->recognizeApplicationName($from)) {
            return $this->finder->findByName($from);
        }

        if ($this->identifierParser->recognizeApplicationInstanceId($from)) {
            return $this->finder->findByInstanceId($from);
        }

        return null;
    }

    public function supports(Configuration\ParamConverter $configuration)
    {
        //TODO check that this class supports the configuration
        return true;
    }
}
