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

namespace DeskPRO\Bundle\AppBundle\CacheWarmer;

use Metadata\MetadataFactoryInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Class JmsSerializerCacheWarmer.
 */
class JmsSerializerCacheWarmer implements CacheWarmerInterface
{
    private $metadataFactory;

    /**
     * Constructor.
     *
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $dirs = [
            DP_ROOT.'/src/Application/DeskPRO/Entity',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Entity',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Serializer/Model',
        ];

        $finder = Finder::create()
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
            if (0 === strpos($class, 'Application/DeskPRO/Entity')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO/Bundle/AppBundle/Entity')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO/Bundle/AppBundle/Serializer/Model')) {
                $this->cacheMetadata($class);
            }
        }
    }

    protected function cacheMetadata($class)
    {
        try {
            $this->metadataFactory->getMetadataForClass($class);
        } catch (\ReflectionException $e) {
            // TODO: we should dive into FQCN to know why it return directories as FQCN.
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
