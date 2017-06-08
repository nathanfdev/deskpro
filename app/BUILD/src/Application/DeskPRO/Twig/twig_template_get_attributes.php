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

if (!function_exists('twig_template_get_attributes')) {
    function twig_template_get_attributes($tpl, $object, $item, array $arguments = [], $type = 'any', $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        static $refl = [];

        // getAttribute declared public on our override class, so we can skip reflection
        if ($tpl instanceof \Application\DeskPRO\Twig\Template) {
            return $tpl->getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
        }

        $className = get_class($tpl);
        if (!isset($refl[$className])) {
            $refl[$className] = new \ReflectionMethod($className, 'getAttribute');
            $refl[$className]->setAccessible(true);
        }

        return $refl[$className]->invoke($tpl, $object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
    }
}
