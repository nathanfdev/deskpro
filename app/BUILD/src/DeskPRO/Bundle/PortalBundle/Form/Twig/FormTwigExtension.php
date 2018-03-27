<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Form\Twig;

class FormTwigExtension extends\Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('str_replace', [$this, 'stringReplace']),
        ];
    }

    public function stringReplace($string, $replace, $with)
    {
        return str_replace($replace, $with, $string);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'form_twig_extension';
    }
}
