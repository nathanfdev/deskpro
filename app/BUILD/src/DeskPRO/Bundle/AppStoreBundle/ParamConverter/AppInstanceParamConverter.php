<?php

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class AppInstanceParamConverter implements ParamConverterInterface
{
    /** @var Infrastructure\ApplicationInstanceDoctrineFinder */
    private $finder;

    /** @var Infrastructure\IdentifierParser */
    private $identifierParser;

    public function __construct(Infrastructure\ApplicationInstanceDoctrineFinder $finder, Infrastructure\IdentifierParser $identifierParser)
    {
        $this->finder           = $finder;
        $this->identifierParser = $identifierParser;
    }

    public function apply(Request $request, Configuration\ParamConverter $configuration)
    {
        $attributeName = $configuration->getName();
        $from          = $request->attributes->get($attributeName, null);

        if (empty($from)) {
            return false;
        }

        $application = $this->convert($from);
        if (!empty($application)) {
            $request->attributes->set($attributeName, $application);

            return true;
        }

        $request->attributes->set($attributeName, null);

        return false;
    }

    /**
     * @param string $from
     *
     * @return Entity\AppStore\App
     */
    private function convert($from)
    {
        $from = urldecode($from);

        if ($this->identifierParser->recognizeNumericIdentifier($from)) {
            return $this->finder->findById($from);
        }

        $appRef = $this->identifierParser->parseApplicationRef($from);
        if (!is_null($appRef)) {
            return $this->finder->findSoleApplicationInstanceByRef($appRef);
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
