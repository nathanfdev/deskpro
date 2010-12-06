<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Twig
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Twig\Extension;

use \Symfony\Component\Templating\Engine;
use \Symfony\Bundle\TwigBundle\TokenParser\HelperTokenParser;

class Helpers extends \Twig_Extension
{
    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return array An array of Twig_TokenParser instances
     */
    public function getTokenParsers()
    {
        return array(
            // {% phrase 'tech.welcome_back_x' with ['Christopher'] %}
            new HelperTokenParser('phrase', '<phrase> [with <arguments:array>]', 'phrase', 'phrase'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'deskpro.helpers';
    }
}
