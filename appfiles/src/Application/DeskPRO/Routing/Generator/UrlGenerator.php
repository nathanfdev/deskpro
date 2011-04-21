<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Routing\Generator;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;

use Application\DeskPRO\App;

/**
 * This URL generator sets a default _locale part with the current Translator locale.
 */
class UrlGenerator extends BaseUrlGenerator
{
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute)
    {
		if (isset($variables['_locale']) && empty($defaults['_locale'])) {
			App::getTranslator()->getLocale()->getLocale();

			// Default
			if ($defaults['_locale'] == 'en_US') {
				unset($defaults['_locale']);
			}
		}

		return parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute);
    }
}
