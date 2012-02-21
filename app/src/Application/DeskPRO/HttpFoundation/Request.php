<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\HttpFoundation;

use Symfony\Component\HttpFoundation\SessionStorage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Request extends \Symfony\Component\HttpFoundation\Request
{
	const PARTIAL_REQUEST_KEY = '_partial';

	protected $url_locale = null;

	/**
	 * When a client sends _partial in POST/GET data, they're requesting a partial result
	 *
	 * For example: more search results, or a page being put into an existing page etc. The actual
	 * meaning of what "partial" is depends on the page.
	 *
	 * Returns either 'partial', or a string value of the _partial (which might be used to denote different
	 * types of partial templates).
	 *
	 * @return bool|string
	 */
	public function isPartialRequest()
	{
		$val = false;

		if ($this->query->has(self::PARTIAL_REQUEST_KEY)) {
			$val = $this->query->get(self::PARTIAL_REQUEST_KEY);
			if (!$val) $val = 'partial';
		} elseif ($this->request->has(self::PARTIAL_REQUEST_KEY)) {
			$val = $this->request->get(self::PARTIAL_REQUEST_KEY);
			if (!$val) $val = 'partial';
		}

		return $val;
	}

	public function isPost()
	{
		return $this->getMethod() == 'POST';
	}

	public function isGet()
	{
		return $this->getMethod() == 'GET';
	}

	/**
	 * Detect the locale in the URL. This is the first /en/ or /en_US/ part of the URL.
	 *
	 * @return string
	 */
	public function getUrlLocale()
	{
		if ($this->url_locale !== null) return $this->url_locale;

		$this->url_locale = false;

		#------------------------------
		# We check for locale prefix in user section
		#------------------------------

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
				$this->url_locale = $locale;
			}
		}

		$this->attributes->set('_locale', $this->url_locale);

		return $this->url_locale;
	}

	public function getServerBaseUrl()
	{
		$base = parent::getBaseUrl();

		if (strpos($base, 'index.php') === false && !isset($GLOBALS['DP_CONFIG']['rewrite_urls'])) {
			$base .= '/index.php';
		}

		return $base;
	}
}
