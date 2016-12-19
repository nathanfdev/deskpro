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

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation\Metadata;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\ActionPermissionsDriver;
use DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataCache;
use DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use DpTest\ApiTestCase;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\CheckMetadataTrait;
use DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock\ActionPermissionsClass;

class MetadataFactoryTest extends ApiTestCase
{
    use CheckMetadataTrait;

    /** @var MetadataFactory */
    protected $factory;

    public function setUp()
    {
        parent::setUp();
        $kernel        = $this->getApiKernel(true);
        $cache         = new MetadataCache($kernel->getContainer()->getParameter('kernel.cache_dir'), 'metadata_cache');
        $driver        = new ActionPermissionsDriver(new AnnotationReader());
        $this->factory = new MetadataFactory($driver, $cache);
    }

    public function testAnnotations()
    {
        $class_metadata = $this->factory->getMetadataForClass(ActionPermissionsClass::class);

        $this->checkMetadata($class_metadata);
    }

    public function testAnnotationsCacheRewrite()
    {
        $class_metadata = $this->factory->getMetadataForClass(ActionPermissionsClass::class, true);

        $this->checkMetadata($class_metadata);
    }
}
