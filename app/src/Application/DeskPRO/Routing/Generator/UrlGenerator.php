<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
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

	/**
	 * This is like generate() except it returns JUST the route. Nothing to do with the current base
	 * path etc is added. This will begin with a slash.
	 *
	 * @param $name
	 * @param array $parameters
	 * @param bool $absolute
	 */
	public function generatePath($name, $parameters = array(), $absolute = false)
	{
		$url = $this->generate($name, $parameters, $absolute);
		$url = preg_replace('#^' . preg_quote($this->context->getBaseUrl(), '#') . '#', '', $url);
		return $url;
	}

	protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute)
	{
		$url = parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute);

		// /file.php/ is a hint to say that we want to serve through the file loader,
		// Any route that is prefixed with /file.php/ has this magic below applied
		if (strpos($url, '/file.php/') !== false) {
			$url = str_replace('/index.php', '', $url);
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
