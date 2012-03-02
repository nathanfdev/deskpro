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
}
