<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use DeskPRO\Component\Util\MapUtils;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * We have a few extensions that implement configureOptions() to set new defaults
 * on known form options:.
 *
 * - DeskPRO\Bundle\PortalBundle\Form\Form\Type\CsrfDoubleSubmitExtension
 * - DeskPRO\Bundle\PortalBundle\Form\Form\Extension\DeskproFormExtension
 *
 * This requires our ext to be added first (so options are configured first).
 * So we added the ability for the tag to define a 'order' property to the tag, and this
 * pass re-orders the form.extension service based on that.
 *
 * Lower ordered items are first. The default is 0. E.g. use >0 number to get them to run after syfmomy.
 *
 * @see Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\FormPass
 */
class FormOrderExtensionsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('form.extension')) {
            return;
        }

        $definition     = $container->getDefinition('form.extension');
        $typeExtensions = $definition->getArgument(2);

        // sorts an array of [something, order_int]
        $sortFn = function ($a, $b) {
            if ($a[1] === $b[1]) {
                return 0;
            }

            return $a[1] < $b[1] ? -1 : 1;
        };

        $orderedTypeExtensions = [];
        foreach ($typeExtensions as $extendedType => $extServices) {
            $orderedServices = [];

            foreach ($extServices as $serviceId) {
                $orderedServices[] = [$serviceId, $this->getOrderForService($container->getDefinition($serviceId))];
            }

            // also put short alias types (e.g. 'form') after full types
            // full types are required in 3.0, but we have a few old ones still using aliases
            if (strpos($extendedType, '\\') !== false) {
                $pri = 0;
            } else {
                $pri = 1;
            }
            $orderedTypeExtensions[$extendedType] = [$orderedServices, $pri];
        }

        uasort($orderedTypeExtensions, $sortFn);

        // this converts [serviceIds, order_int] to just service_ids
        $orderedTypeExtensions = MapUtils::mapValues($orderedTypeExtensions, function ($k, $v) use ($sortFn) {
            usort($v[0], $sortFn);

            return array_map(function ($v) {
                return $v[0];
            }, $v[0]);
        });

        // replace the sorted array back into the DI def
        $definition->replaceArgument(2, $orderedTypeExtensions);
    }

    /**
     * @param Definition $extDef
     *
     * @return int
     */
    private function getOrderForService(Definition $extDef)
    {
        if (!$extDef->hasTag('form.type_extension')) {
            return 0;
        }

        $tag = $extDef->getTag('form.type_extension');

        // symfony go low so other (e.g. FOS) go after
        if (empty($tag[0]['order']) && strpos($extDef->getClass(), 'Symfony\Component\Form\Extension\\') === 0) {
            return -1000;
        }

        if (empty($tag[0]['order'])) {
            return 0;
        }

        return (int) $tag[0]['order'];
    }
}
