<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 */
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
