<?php

namespace DeskPRO\Bundle\AppBundle\Util;

use Symfony\Component\Finder\Finder;

/**
 * Class ApiControllersFinder.
 */
class ApiControllersFinder
{
    /**
     * @var array
     */
    protected $classes = [];

    /**
     * @return array
     */
    public function getClasses()
    {
        if (!$this->classes) {
            $finder = Finder::create()
                ->in([
                    DP_ROOT.'/src/DeskPRO/Bundle/ApiBundle/Controller',
                    DP_ROOT.'/src/Application/LegacyApiBundle/Controller',
                ])
                ->name('*.php')
            ;

            foreach ($finder as $f) {
                require_once $f->getRealPath();
            }

            foreach (get_declared_classes() as $class) {
                if (0 === strpos($class, 'DeskPRO\\Bundle\\ApiBundle\\Controller')) {
                    $this->classes[] = $class;
                } elseif (0 === strpos($class, 'Application\\LegacyApiBundle\\Controller')) {
                    $this->classes[] = $class;
                }
            }
        }

        return $this->classes;
    }
}
