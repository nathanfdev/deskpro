<?php

namespace DeskPRO\Bundle\AppBundle\CacheWarmer;

use Doctrine\Common\Annotations\AnnotationException;
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
            DP_ROOT.'/src/Application/DeskPRO/Domain',
            DP_ROOT.'/src/Application/DeskPRO/Entity',
            DP_ROOT.'/src/Application/DeskPRO/Tickets/Triggers',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Entity',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Serializer/Model',
            DP_ROOT.'/src/DeskPRO/Bundle/ApiBundle/Model',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Content',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/CountBadge',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Serializer',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Settings/Model',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Ticket',
            DP_ROOT.'/src/DeskPRO/Bundle/MessengerBundle/Settings',
            DP_ROOT.'/src/DeskPRO/Bundle/ReportBundle/Serializer/Model',
            DP_ROOT.'/src/DeskPRO/Bundle/SendmailBundle/View/Model',
            DP_ROOT.'/src/Orb/Util',
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
            if (0 === strpos($class, 'Application\\DeskPRO\\Entity')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\AppBundle\\Entity')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'Application\\DeskPRO\\Domain')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\AppBundle\\Serializer\\Model')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'Application\\DeskPRO\\Tickets\\Triggers')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\ApiBundle\\Model')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\AppBundle\\Content')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\AppBundle\\CountBadge')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\AppBundle\\Serializer')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\AppBundle\\Settings\\Model')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\AppBundle\\Ticket')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\MessengerBundle\\Settings')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\ReportBundle\\Serializer\\Model')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'ArrayObject')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'DeskPRO\\Bundle\\SendmailBundle\\View\\Model')) {
                $this->cacheMetadata($class);
            } elseif (0 === strpos($class, 'Orb\\Util')) {
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
        } catch (AnnotationException $e) {
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
