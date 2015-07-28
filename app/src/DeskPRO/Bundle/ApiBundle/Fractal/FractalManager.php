<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Fractal;

use League\Fractal\Manager;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;
use League\Fractal\TransformerAbstract;
use Swagger\Annotations\AbstractAnnotation;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Our custom FractalManager is container aware, and it allows you to reference strings as transformers.
 *
 * The string is used to make a service container service name. You can pass in "sandbox_widget" as a transformer and
 * it will fetch "api_transformer.sandbox_widget" from the container if it exists.
 */
class FractalManager extends Manager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function expandTransformerServiceName($short_name)
    {
        return 'api_transformer.' . $short_name;
    }

    public function createData(ResourceInterface $resource, $scopeIdentifier = null, Scope $parentScopeInstance = null)
    {
        // use a custom scope
        $scopeInstance = new FractalScope($this, $resource, $scopeIdentifier);

        // Update scope history
        if ($parentScopeInstance !== null) {
            // This will be the new children list of parents (parents parents, plus the parent)
            /** @var FractalScope|TransformerAbstract $parentScopeInstance */
            $scopeArray = $parentScopeInstance->getParentScopes();
            $scopeArray[] = $parentScopeInstance->getCurrentScope();
            $scopeInstance->setParentScopes($scopeArray);
        }

        return $scopeInstance;
    }

    /**
     * Given a transformer object or a string (a service short name without the prefix, like "sandbox_widget") this
     * will return the Transformer object to use.
     *
     * @param $transformer
     * @param $data
     * @return TransformerAbstract
     */
    public function resolveTransformer($transformer, $data)
    {
        if (
            // if the transformer is a string and we have a service for it in the container, resolve it
            is_string($transformer)
            && $this->getContainer()->has($transformer_service_name = $this->expandTransformerServiceName($transformer))
        ) {
            $transformer = $this->container->get($transformer_service_name);
            if ($transformer instanceof ContainerAwareInterface) {
                // you can make your transformer implement ContainerAwareInterface (but we prefer you inject in the service definition)
                $transformer->setContainer($this->container);
            }
        }

        return $transformer;
    }
}
