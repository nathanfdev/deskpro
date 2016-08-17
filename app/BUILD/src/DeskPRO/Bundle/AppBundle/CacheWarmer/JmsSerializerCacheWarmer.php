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

use Gnugat\NomoSpaco\File\FileRepository;
use Gnugat\NomoSpaco\FqcnRepository;
use Gnugat\NomoSpaco\Token\ParserFactory;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Class JmsSerializerCacheWarmer.
 */
class JmsSerializerCacheWarmer implements CacheWarmerInterface
{
    private $metadataFactory;

    /**
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param string $cacheDir
     */
    public function warmUp($cacheDir)
    {
        $fqcnRepo = new FqcnRepository(new FileRepository(), new ParserFactory());

        $dirs = [
            DP_ROOT.'/src/Application/DeskPRO/Entity',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Entity',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Serializer/Model',
        ];

        foreach ($dirs as $dir) {
            $fqcns = @$fqcnRepo->findIn($dir);
            foreach ($fqcns as $fqcn) {
                try {
                    $this->metadataFactory->getMetadataForClass($fqcn);
                } catch (\ReflectionException $e) {
                    // TODO: we should dive into FQCN to know why it return directories as FQCN.
                }
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
}
