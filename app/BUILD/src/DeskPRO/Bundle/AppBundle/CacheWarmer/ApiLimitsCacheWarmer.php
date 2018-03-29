<?php

namespace DeskPRO\Bundle\AppBundle\CacheWarmer;

use DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException;
use DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataFactory;
use DeskPRO\Bundle\AppBundle\Util\ApiControllersFinder;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Class ApiLimitsCacheWarmer.
 */
class ApiLimitsCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var ApiControllersFinder
     */
    protected $finder;

    /**
     * Constructor.
     *
     * @param MetadataFactory      $metadataFactory
     * @param ApiControllersFinder $finder
     */
    public function __construct(MetadataFactory $metadataFactory, ApiControllersFinder $finder)
    {
        $this->metadataFactory = $metadataFactory;
        $this->finder          = $finder;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->finder->getClasses() as $class) {
            try {
                $this->metadataFactory->getMetadataForClass($class, true);
            } catch (AbstractClassException $e) {
                // There is nothing to do. Or just output it
            } catch (\ReflectionException $e) {
                // TODO: we should dive into FQCN to know why it return directories as FQCN.
            }
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
