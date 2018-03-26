<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator\Content;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\ContentAbstract;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkConfigRepoInterface;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGeneratorInterface;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractContentLinkGenerator implements LinkGeneratorInterface
{
    /** @var EntityManager */
    protected $em;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var LinkConfigRepoInterface
     */
    protected $configRepo;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     * @param UrlGeneratorInterface   $urlGenerator
     * @param PropertyAccessor        $propertyAccessor
     * @param LinkConfigRepoInterface $configRepo
     */
    public function __construct(
        EntityManager $em,
        UrlGeneratorInterface $urlGenerator,
        PropertyAccessor $propertyAccessor,
        LinkConfigRepoInterface $configRepo
    ) {
        $this->em               = $em;
        $this->urlGenerator     = $urlGenerator;
        $this->configRepo       = $configRepo;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($object, $type, $context, $extraParams, $referenceType)
    {
        $config = $this->configRepo->getRouteInfo($object, ObjectRouter::CONTEXT_PORTAL, $type);

        $routeName = $config['route'];

        $routeParams = [];
        foreach ($config['route_param_map'] as $paramName => $propertyPath) {
            $routeParams[$paramName] = $this->propertyAccessor->getValue(
                $object, $propertyPath
            );
        }

        return $this->urlGenerator->generate(
            $routeName,
            array_merge($routeParams, ['brand' => $this->getBrand($object)]),
            $referenceType
        );
    }

    /**
     * @param ContentAbstract $object
     *
     * @return Brand
     */
    abstract protected function getBrand(ContentAbstract $object);
}
