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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class AppExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $config);

        $types = [];

        if (array_key_exists('data_serializer', $config)) {
            if (array_key_exists('types', $config['data_serializer'])) {
                foreach ($config['data_serializer']['types'] as $type => $matchers) {
                    $classes = [];
                    if (isset($matchers['classes'])) {
                        foreach ($matchers['classes'] as $class) {
                            $classes[] = $class;
                            // automatically add the doctrine proxy name to the map as well
                            $classes[] = 'Proxies\\__CG__\\'.$class;
                        }
                    }
                    $types[$type] = [
                        'classes' => $classes,
                    ];
                }
            }
        }

        $container->setParameter('data_serializer.types', $types);
        if (array_key_exists('notification', $config)) {
            if (array_key_exists('strategies', $config['notification'])) {
                $container->setParameter('notification.settings', $config['notification']['strategies']);
            }
        }

        $loader = new YamlDirectoryLoader($container);
        $loader->loadDir(__DIR__.'/../Resources/config/services');

        // use our translator
        $container->setAlias('translator', 'translator.noop');

        $this->applyBackwardsCompatibilityRequirements($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function applyBackwardsCompatibilityRequirements(ContainerBuilder $container)
    {
        $container->setAlias('deskpro.blob_storage', 'blob.storage');
    }
}
