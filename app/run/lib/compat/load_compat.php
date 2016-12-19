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
    function twig_template_get_attributes($tpl, $object, $item, array $arguments = array(), $type = 'any', $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        static $refl = [];

        $className = get_class($tpl);
        if (!isset($refl[$className])) {
            $refl[$className] = new \ReflectionMethod($className, 'getAttribute');
            $refl[$className]->setAccessible(true);
        }

        return $refl[$className]->invoke($tpl, $object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
    }
}
