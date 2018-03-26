<?php

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
