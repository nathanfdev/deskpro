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
 * @subpackage
 */

namespace Application\LanguageBundle\Language;


use Application\DeskPRO\Translate\Translate;

class LanguageManager
{
	/**
	 * @var \Application\DeskPRO\Translate\Translate
	 */
	private $translate;

	public function __construct(Translate $translate)
	{
		var_dump($translate->phrase('user.general.go_back'));exit;
		$this->translate = $translate;
	}


	/**
	 * @return bool
	 */
	public function isMultiLanguagePortal()
	{
		return false;
	}

	/**
	 * @param string $lang_code an arbitrary lang string
	 * @return bool
	 */
	public function isLanguageSupported($lang_code)
	{
		$lang_code = $this->normalizeLanguageCode($lang_code);
	}


	/**
	 * @param string $lang_code an arbitrary lang string
	 * @return \Application\DeskPRO\Entity\Language
	 */
	public function getLanguage($lang_code)
	{
		$lang_code = $this->normalizeLanguageCode($lang_code);
	}


	/**
	 * Turns an arbitrary lang string into a more normailzed lang string that we use internally for URLs.
	 *
	 * @param string $lang_code
	 * @return string
	 */
	public function normalizeLanguageCode($lang_code)
	{
		return $lang_code;
	}
}
 