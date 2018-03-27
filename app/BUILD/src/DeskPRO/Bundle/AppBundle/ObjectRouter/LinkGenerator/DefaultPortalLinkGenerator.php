<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator;

use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkConfigRepoInterface;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGeneratorInterface;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class DefaultPortalLinkGenerator.
 */
class DefaultPortalLinkGenerator implements LinkGeneratorInterface
{
    /**
     * @var LinkConfigRepoInterface
     */
    private $config_repo;

    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var PropertyAccessor
     */
    private $property_accessor;

    /**
     * Constructor.
     *
     * @param LinkConfigRepoInterface $config_repo
     * @param UrlGeneratorInterface   $url_generator
     * @param PropertyAccessor        $property_accessor
     */
    public function __construct(
        LinkConfigRepoInterface $config_repo,
        UrlGeneratorInterface   $url_generator,
        PropertyAccessor        $property_accessor
    ) {
        $this->config_repo       = $config_repo;
        $this->url_generator     = $url_generator;
        $this->property_accessor = $property_accessor;
    }

    /**
     * Supports PORTAL context if the config is NOT for a CUSTOM link.
     *
     * {@inheritdoc}
     */
    public function supports($object, $type, $context)
    {
        if (ObjectRouter::CONTEXT_PORTAL !== $context) {
            return false;
        }

        $config = $this->config_repo->getRouteInfo($object, $context, $type);

        return $config !== ObjectRouter::CONFIG_CUSTOM;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($object, $type, $context, $extra_params, $reference_type)
    {
        $config = $this->config_repo->getRouteInfo($object, ObjectRouter::CONTEXT_PORTAL, $type);

        $route_name = $config['route'];

        $route_params = [];
        foreach ($config['route_param_map'] as $param_name => $property_path) {
            $route_params[$param_name] = $this->property_accessor->getValue(
                $object, $property_path
            );
        }

        return $this->url_generator->generate(
            $route_name,
            array_merge($route_params, $extra_params),
            $reference_type
        );
    }
}
