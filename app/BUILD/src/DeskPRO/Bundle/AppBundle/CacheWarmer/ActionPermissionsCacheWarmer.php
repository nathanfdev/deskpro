<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\ActionPermissionsMetadataFactory;
use DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException;
use Gnugat\NomoSpaco\File\FileRepository;
use Gnugat\NomoSpaco\FqcnRepository;
use Gnugat\NomoSpaco\Token\ParserFactory;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Class ActionPermissionsCacheWarmer.
 */
class ActionPermissionsCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var ActionPermissionsMetadataFactory
     */
    protected $metadata_factory;

    /**
     * @param ActionPermissionsMetadataFactory $metadata_factory
     */
    public function __construct(ActionPermissionsMetadataFactory $metadata_factory)
    {
        $this->metadata_factory = $metadata_factory;
    }

    /**
     * @param string $cacheDir
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->getClasses() as $class) {
            try {
                $metadata[] = $this->metadata_factory->getMetadataForClass($class, true);
            } catch (AbstractClassException $e) {
                // There is nothing to do. Or just output it
            } catch (\ReflectionException $e) {
                // TODO: we should dive into FQCN to know why it return directories as FQCN.
            }
        }
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * @param $cacheDir
     *
     * @return string
     */
    protected function getCacheDir($cacheDir)
    {
        $cacheDir = $cacheDir.DIRECTORY_SEPARATOR.'api_permissions';
        if (!file_exists($cacheDir)) {
            if (!$rs = @mkdir($cacheDir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $cacheDir));
            }
        }

        return $cacheDir;
    }

    /**
     * @return array
     */
    protected function getClasses()
    {
        $fqcn_repo = new FqcnRepository(new FileRepository(), new ParserFactory());

        return array_merge(
            @$fqcn_repo->findIn(DP_ROOT.'/src/DeskPRO/Bundle/ApiBundle/Controller'),
            @$fqcn_repo->findIn(DP_ROOT.'/src/Application/LegacyApiBundle/Controller')
        );
    }
}
