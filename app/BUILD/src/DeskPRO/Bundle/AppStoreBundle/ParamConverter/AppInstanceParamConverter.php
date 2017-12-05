<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
