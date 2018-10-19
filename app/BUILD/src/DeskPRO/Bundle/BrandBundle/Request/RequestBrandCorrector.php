<?php

namespace DeskPRO\Bundle\BrandBundle\Request;

use Application\DeskPRO\Entity\Brand;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestBrandCorrector.
 */
class RequestBrandCorrector
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Request $request
     */
    public function patchRequest(Request $request)
    {
        if (!preg_match('#^/b/([\w\d-]+?)(/{1}.*|$)$#', $request->getPathInfo(), $matches)) {
            return;
        }

        $brand = $this->em->getRepository(Brand::class)->findOneBy(['slug' => $matches[1]]);
        if (!$brand) {
            return;
        }

        // set base url
        $slugPath   = '/b/'.$brand->getSlug();
        $reflection = new \ReflectionClass(Request::class);

        if ($request->getBaseUrl() !== $slugPath) {
            $property = $reflection->getProperty('baseUrl');
            $property->setAccessible(true);
            $property->setValue($request, rtrim($request->getBaseUrl(), '/').$slugPath);
            $property->setAccessible(false);
        }

        $originalPathInfo = $request->getPathInfo();
        $brandPathInfo    = preg_replace("#^$slugPath#", '', $originalPathInfo);
        if (!$brandPathInfo) {
            $brandPathInfo = '/';
        }

        $property = $reflection->getProperty('pathInfo');
        $property->setAccessible(true);
        $property->setValue($request, $brandPathInfo);
        $property->setAccessible(false);

        $request->attributes->set('_dp_brand_slug', $brand->getSlug());
        $request->attributes->set('_dp_brand_slug_path', $slugPath);
    }
}
