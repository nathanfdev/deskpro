<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Routing\Matcher;

use Symfony\Component\Routing\Matcher\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Matcher\Exception\NotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use Orb\Util\Strings;

class UrlMatcher extends \Symfony\Component\Routing\Matcher\UrlMatcher
{
	protected $got_locale = null;

	public function match($pathinfo)
	{
		#------------------------------
		# We check for locale prefix in user section
		#------------------------------

		$this->got_locale = null;

		$nocheck_sections = array(
			'/agent',
			'/admin',
			'/dev',
			'/api'
		);

		$check_for_locale = true;
		foreach ($nocheck_sections as $s) {
			if (strpos($pathinfo, $s) === 0) {
				$check_for_locale = false;
			}
		}

		if ($check_for_locale) {
			$locale = Strings::extractRegexMatch('#^/([a-z]{2})/#', $pathinfo, 1);
			if ($locale) {
				$locale = Strings::extractRegexMatch('#^/([a-z]{2}_[A-Z]{2}/#', $pathinfo, 1);
			}

			if ($locale) {
				$this->got_locale = $locale;

				// Remove it from the 
				$pathinfo = preg_replace('#^/(.*?)/#', '/', $pathinfo);
			}
		}

		return parent::match($pathinfo);
	}

	protected function mergeDefaults($params, $defaults)
	{
		$parameters = array_merge($this->defaults, $defaults);
		foreach ($params as $key => $value) {
			if (!is_int($key)) {
				$parameters[$key] = urldecode($value);
			}
		}

		return $parameters;
	}
}
