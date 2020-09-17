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
        $namespaces = [
            'Application\\DeskPRO\\Entity',
            'DeskPRO\\Bundle\\AppBundle\\Entity',
            'Application\\DeskPRO\\Domain',
            'DeskPRO\\Bundle\\AppBundle\\Serializer\\Model',
            'Application\\DeskPRO\\Tickets\\Triggers',
            'DeskPRO\\Bundle\\ApiBundle\\Model',
            'DeskPRO\\Bundle\\AppBundle\\Content',
            'DeskPRO\\Bundle\\AppBundle\\CountBadge',
            'DeskPRO\\Bundle\\AppBundle\\Serializer',
            'DeskPRO\\Bundle\\AppBundle\\Settings\\Model',
            'DeskPRO\\Bundle\\AppBundle\\Ticket',
            'DeskPRO\\Bundle\\MessengerBundle\\Settings',
            'DeskPRO\\Bundle\\ReportBundle\\Serializer\\Model',
            'ArrayObject',
            'DeskPRO\\Bundle\\SendmailBundle\\View\\Model',
            'Orb\\Util',
        ];

        $dirs = array_map(function ($namespace) {
            return DP_ROOT.'/src/'.str_replace('\\', '/', $namespace);
        }, array_filter($namespaces, function ($namespace) {
            return false !== strpos($namespace, '\\');
        }));

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
            $canWarm = array_reduce($namespaces, function ($result, $namespace) use ($class) {
                return $result || 0 === strpos($class, $namespace);
            }, false);

            if ($canWarm) {
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
