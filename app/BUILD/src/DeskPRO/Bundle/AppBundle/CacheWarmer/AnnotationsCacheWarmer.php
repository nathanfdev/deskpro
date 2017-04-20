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

namespace DeskPRO\Bundle\AppBundle\CacheWarmer;

use Doctrine\Common\Annotations\AnnotationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * This finds all FQCN in the project, and asks the annotation reader for its annotations.
 *
 * This will force the Annotation FileCacheReader to save the annotations to disk in the cache for every class
 * in the project. This avoids any attempt to write to the cache when we try to get annotations for a class because
 * the annotations are already cached.
 */
class AnnotationsCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true; // we dont want to do this in dev env
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $dirs = [
            DP_ROOT.'/src/Application',
            DP_ROOT.'/src/DeskPRO',
        ];

        $annotaionReader = $this->container->get('annotation_reader');
        $finder          = Finder::create()
            ->in($dirs)
            ->name('*.php')
            ->notPath('/Dpql\/build.+/')
            ->notPath('/Resources/')
            ->notPath('/InstallBundle\/Data/')
        ;

        foreach ($finder as $f) {
            require_once $f->getRealPath();
        }

        foreach (get_declared_classes() as $class) {
            if (0 !== strpos($class, 'DeskPRO') && 0 !== strpos($class, 'Application')) {
                continue;
            }

            $reflection = new \ReflectionClass($class);
            try {
                $annotaionReader->getClassAnnotations($reflection);
            } catch (AnnotationException $e) {
                // todo we have lots of @option that throw AnnotationException. ignore or cleanup?
            }
        }
    }
}
