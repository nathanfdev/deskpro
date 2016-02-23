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

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\ActionPermissionsDriver;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Mock\ActionPermissionsClass;
use DeskPRO\Bundle\AppBundle\Annotation\MetadataCache;
use Doctrine\Common\Annotations\AnnotationReader;
use DpTest\ApiTestCase;
use Metadata\ClassMetadata;

class ActionPermissionsMetadataFactoryTest extends ApiTestCase
{
    /** @var ActionPermissionsMetadataFactory */
    protected $factory;

    public function setUp()
    {
        parent::setUp();
        $kernel        = $this->getApiKernel(true);
        $cache         = new MetadataCache($kernel->getContainer()->getParameter('kernel.cache_dir'));
        $driver        = new ActionPermissionsDriver(new AnnotationReader());
        $this->factory = new ActionPermissionsMetadataFactory($driver, $cache);
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

    protected function checkMetadata(ClassMetadata $class_metadata)
    {
        $this->assertEquals(
            ['session', 'token'],
            $class_metadata->methodMetadata['inheritAction']->getModes(),
            'Inherit class ApiModes problem'
        );
        $this->assertEquals(
            ['class.mock', 'desk_pro.bundle.app_bundle.annotation.action_permissions.mock.action_permissions_class.inherit'],
            $class_metadata->methodMetadata['inheritAction']->getTags(),
            'Inherit class ApiTag problem'
        );

        $this->assertEquals(
            ['session', 'token', 'key'],
            $class_metadata->methodMetadata['overrideModesAction']->getModes(),
            'overrideModes ApiModes problem'
        );
        $this->assertEquals(
            ['class.mock', 'desk_pro.bundle.app_bundle.annotation.action_permissions.mock.action_permissions_class.override_modes'],
            $class_metadata->methodMetadata['overrideModesAction']->getTags(),
            'overrideModes ApiTags problem'
        );

        $this->assertEquals(
            ['session', 'token'],
            $class_metadata->methodMetadata['overrideTagsAction']->getModes(),
            'overrideTags ApiModes problem'
        );
        $this->assertEquals(
            ['class.overridden', 'desk_pro.bundle.app_bundle.annotation.action_permissions.mock.action_permissions_class.override_tags'],
            $class_metadata->methodMetadata['overrideTagsAction']->getTags(),
            'overrideTags ApiTags problem'
        );

        $this->assertEquals(
            ['token', 'key'],
            $class_metadata->methodMetadata['overrideBothAction']->getModes(),
            'overrideBoth ApiModes problem'
        );
        $this->assertEquals(
            ['class.overridden', 'class.overridden2', 'desk_pro.bundle.app_bundle.annotation.action_permissions.mock.action_permissions_class.override_both'],
            $class_metadata->methodMetadata['overrideBothAction']->getTags(),
            'overrideBoth ApiTags problem'
        );
    }
}
