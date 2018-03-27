<?php

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class AppParamConverter implements ParamConverterInterface
{
    /** @var Infrastructure\ApplicationDoctrineFinder */
    private $finder;

    /** @var Infrastructure\IdentifierParser */
    private $identifierParser;

    public function __construct(Infrastructure\ApplicationDoctrineFinder $finder, Infrastructure\IdentifierParser $identifierParser)
    {
        $this->finder           = $finder;
        $this->identifierParser = $identifierParser;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $configOptions = $configuration->getOptions();

        $paramName     = $configuration->getName();
        $attributeName = is_array($configOptions) && array_key_exists('attribute', $configOptions) ? $configOptions['attribute'] : $paramName;
        $from          = $request->attributes->get($attributeName);
        if (empty($from)) {
            return false;
        }

        $numericIdentifierStrategy = is_array($configOptions) && array_key_exists('numericId', $configOptions) ? $configOptions['numericId'] : 'application';
        $application               = $this->convert($from, $numericIdentifierStrategy);
        $request->attributes->set($paramName, $application);

        return true;
    }

    /**
     * @param string      $from
     * @param string|null $numericIdentifierStrategy
     *
     * @return Entity\AppStore\App
     */
    private function convert($from, $numericIdentifierStrategy = null)
    {
        $from = urldecode($from);

        if ($this->identifierParser->recognizeNumericIdentifier($from) && $numericIdentifierStrategy === 'instanceId') {
            return $this->finder->findByInstanceId($from);
        }

        $appRef = $this->identifierParser->parseApplicationRef($from);
        if (!is_null($appRef)) {
            return $this->finder->findByReference($appRef);
        }

        return null;
    }

    public function supports(Configuration\ParamConverter $configuration)
    {
        //TODO check that this class supports the configuration
        // todo temp excluded tag request until proper statement is added
        return $configuration->getClass() !== TagRequest::class;
    }
}
