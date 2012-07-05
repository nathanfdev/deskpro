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

namespace Application\DeskPRO\Languages\Build;

use Application\DeskPRO\Languages\LangPackInfo;
use Orb\Log\Logger;
use Symfony\Component\HttpKernel\Util\Filesystem as FilesystemUtil;

abstract class AbstractBuild
{
	/**
	 * @var \Application\DeskPRO\Languages\LangPackInfo
	 */
	private $langinfo;

	/**
	 * @var \Orb\Log\Logger
	 */
	private $logger;


	/**
	 * Build a language
	 *
	 * @param string $id The standard DeskPRO ID for the language
	 */
	abstract public function buildLanguage($id);

	/**
	 * @return array
	 */
	public function getDefaultSections()
	{
		return array('admin', 'agent', 'user');
	}


	/**
	 * @param string $section
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function getDefaultCategories($section)
	{
		switch ($section) {
			case 'user':  return array('chat', 'defaults', 'downloads', 'email_subjects', 'emails', 'error', 'feedback', 'general', 'knowledgebase', 'news', 'portal', 'profile', 'tickets', 'time', 'widget');
			case 'agent': return array('chat', 'deal', 'defaults', 'emails', 'feedback', 'general', 'interface', 'login', 'media', 'organizations', 'people', 'publish', 'report', 'search', 'settings', 'tasks', 'tickets', 'twitter', 'userchat');
			case 'admin': return array('agents', 'api', 'banning', 'billing', 'custom_fields', 'departments', 'designer', 'feedback', 'gateway', 'general', 'languages', 'license', 'logs', 'menu', 'plugins', 'portal', 'products', 'server', 'settings', 'setup', 'templates', 'tickets', 'twitter', 'user_groups', 'user_registration', 'user_rules');
		}

		throw new \InvalidArgumentException("Invalid section $section");
	}


	/**
	 * @return \Orb\Log\Logger
	 */
	public function getLogger()
	{
		if (!$this->logger) {
			$this->logger = new \Orb\Log\Logger();
		}

		return $this->logger;
	}


	/**
	 * @param \Orb\Log\Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @return LangPackInfo
	 */
	public function getLangPackInfo()
	{
		if (!$this->langinfo) {
			$this->langinfo = new LangPackInfo();
		}

		return $this->langinfo;
	}


	/**
	 * @param LangPackInfo $langinfo
	 */
	public function setLangPackInfo(LangPackInfo $langinfo)
	{
		$this->langinfo = $langinfo;
	}


	/**
	 * @param string $id
	 * @param string $section
	 * @param string $category
	 */
	public function writeLangFile($id, $section, $category, array $phrases)
	{
		$dir = $this->getLangPackInfo()->getLangDir() . '/' . $id . '/' . $section;
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		$file = $dir . '/' . $category . '.php';

		if (file_exists($file)) {
			unlink($file);
		}

		$longest = 0;
		foreach ($phrases as $phrase_id => $string) {
			$len = strlen($phrase_id);
			if ($len > $longest) {
				$longest = $len;
			}
		}

		$longest += 4;

		ksort($phrases, \SORT_STRING);

		$php = array("<?php return array(\n");
		foreach ($phrases as $phrase_id => $string) {
			$php[] = sprintf("\t%-{$longest}s => %s,\n", "'$phrase_id'", var_export($string, true));
		}

		$php[] = ");\n";

		$php = implode('', $php);

		file_put_contents($file, $php);
		chmod($file, 0644);

		$this->getLogger()->logInfo('Wrote file: ' . $file);

		return $file;
	}

	/**
	 * Clears out a lang from the filesystem.
	 *
	 * @param string $id
	 */
	public function clearLang($id)
	{
		$dir = $this->getLangPackInfo()->getLangDir() . '/' . $id;

		// Nothing to do
		if (!is_dir($dir)) {
			return;
		}

		$fileutil = new FilesystemUtil();
		$fileutil->remove($dir);
	}
}