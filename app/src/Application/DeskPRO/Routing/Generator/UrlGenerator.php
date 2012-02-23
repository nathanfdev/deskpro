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
use Application\DeskPRO\Routing\Generator\ObjectUrlGenerator;
use Orb\Util\Strings;

use Application\DeskPRO\App;

/**
 * This URL generator sets a default _locale part with the current Translator locale.
 */
class UrlGenerator extends BaseUrlGenerator
{
	protected $object_url_generator = null;

	protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute)
	{
		$url = parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute);

		// /file.php/ is a hint to say that we want to serve through the file loader,
		// Any route that is prefixed with /file.php/ has this magic below applied
		if (strpos($url, '/file.php/') !== false) {
			if (!(isset($GLOBALS['DP_CONFIG']['rewrite_urls']) && $GLOBALS['DP_CONFIG']['rewrite_urls'])) {
				$url = str_replace('/index.php', '', $url);
			}
		}

		return $url;
	}

	public function getObjectUrlGenerator()
	{
		if ($this->object_url_generator !== null) return $this->object_url_generator;

		$this->object_url_generator = new ObjectUrlGenerator($this);
		return $this->object_url_generator;
	}

	public function generateObjectUrl($object, array $params = array(), $context = null)
	{
		return $this->getObjectUrlGenerator()->generateObjectUrl($object, $params, $context);
	}

	/*
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
	*/
}
