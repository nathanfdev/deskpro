<?php

/*
 * This is a hack to normalize the Twig environment.
 *
 * Twig caches and classnames change depending on if the ctwig ext is installed.
 * This is a problem because it means the compiled classname changes if the status
 * of ctwig changes. E.g., if you make a custom template and then later install
 * ctwig, the compiled code in the db will fail because the classname is old.
 *
 * So to get around this, we define twig_template_get_attributes ourselves
 * which just falls-back on the normal getAttribute method.
 *
 * (Unfortunately getAttribute is protected so we need Reflection to call it.)
 */

if (!function_exists('twig_template_get_attributes')) {

    // This is defined because Deskpro warms twig caches with expectation that twig_template_get_attributes is defined (from c ext)
    // but it might not be if customer doesnt have it.
    // So this is a wrapper around getAttribute (which is what is used when c ext isnt installed).

    function twig_template_get_attributes($tpl, $object, $item, array $arguments = array(), $type = 'any', $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        static $refl = [];

        // From this build onwards, Template have public getAttribute so we
        // don't need to use slow reflection
        if (defined('DP_ACTIVE_BUILD') && DP_ACTIVE_BUILD >= 27928) {
            if ($tpl instanceof \Application\DeskPRO\Twig\Template || $tpl instanceof DeskPRO\Bundle\PortalBundle\Twig\Template) {
                return $tpl->getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
            }
        }

        $className = get_class($tpl);
        if (!isset($refl[$className])) {
            $refl[$className] = new \ReflectionMethod($className, 'getAttribute');
            $refl[$className]->setAccessible(true);
        }
        return $refl[$className]->invoke($tpl, $object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
    }
}
