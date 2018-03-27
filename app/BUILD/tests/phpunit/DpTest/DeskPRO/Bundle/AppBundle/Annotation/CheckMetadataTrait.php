<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation;

use Metadata\ClassMetadata;

trait CheckMetadataTrait
{
    protected function checkMetadata(ClassMetadata $class_metadata)
    {
        $this->assertEquals(
            ['session', 'token'],
            $class_metadata->methodMetadata['inheritAction']->getModes(),
            'Inherit class ApiModes problem'
        );
        $this->assertEquals(
            ['class.mock', 'dp_test.desk_pro.bundle.app_bundle.annotation.mock.action_permissions_class.inherit'],
            $class_metadata->methodMetadata['inheritAction']->getTags(),
            'Inherit class ApiTag problem'
        );

        $this->assertEquals(
            ['session', 'token', 'key'],
            $class_metadata->methodMetadata['overrideModesAction']->getModes(),
            'overrideModes ApiModes problem'
        );
        $this->assertEquals(
            ['class.mock', 'dp_test.desk_pro.bundle.app_bundle.annotation.mock.action_permissions_class.override_modes'],
            $class_metadata->methodMetadata['overrideModesAction']->getTags(),
            'overrideModes ApiTags problem'
        );

        $this->assertEquals(
            ['session', 'token'],
            $class_metadata->methodMetadata['overrideTagsAction']->getModes(),
            'overrideTags ApiModes problem'
        );
        $this->assertEquals(
            ['class.overridden', 'dp_test.desk_pro.bundle.app_bundle.annotation.mock.action_permissions_class.override_tags'],
            $class_metadata->methodMetadata['overrideTagsAction']->getTags(),
            'overrideTags ApiTags problem'
        );

        $this->assertEquals(
            ['token', 'key'],
            $class_metadata->methodMetadata['overrideBothAction']->getModes(),
            'overrideBoth ApiModes problem'
        );
        $this->assertEquals(
            ['class.overridden', 'class.overridden2', 'dp_test.desk_pro.bundle.app_bundle.annotation.mock.action_permissions_class.override_both'],
            $class_metadata->methodMetadata['overrideBothAction']->getTags(),
            'overrideBoth ApiTags problem'
        );
    }
}
