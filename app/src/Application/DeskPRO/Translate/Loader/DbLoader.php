<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Translate
 */

namespace Application\DeskPRO\Translate\Loader;

/**
 * Loads phrases from the database.
 *
 * This is an eager loader. All phrases are loaded the first time it is called because
 * 99% of all phrases are NOT in the database, so its a waste to issue multiple queries
 * for lang, so we just do one.
 */
class DbLoader implements LoaderInterface
{
	/**
	 * Plain database connection for raw queries
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $dbconn;

	/**
	 * @var array
	 */
	protected $loaded = null;

	/**
	 * @var int
	 */
	protected $default_lang_id = 1;

	/**
	 * @param \Application\DeskPRO\DBAL\Connection $dbconn
	 */
	public function __construct(\Application\DeskPRO\DBAL\Connection $dbconn)
	{
		$this->dbconn = $dbconn;
	}

	private function returnPhrases($groups, $language, array $loaded_phrases = null)
	{
		$phrases = array();

		// Langs to fetch in order of pri
		$langs = array();
		if ($language) {
			$langs[] = $language->getId(); // the chosen lang
		}
		$langs[] = $this->default_lang_id; // default deskpro lang
		$langs[] = 0; // system use

		foreach ($langs as $lid) {
			foreach ($groups as $g) {
				if (empty($this->loaded[$lid][$g])) continue;

				if ($lid != $this->default_lang_id || ($language && $language->getId() == $lid)) {
					$phrases = array_merge($phrases, $this->loaded[$lid][$g]);
				} else {
					// For default custom phrases, we need to make sure
					// we arent overriding a language with a custom english.
					// Case: An English phrase is overriden, user is using German,
					//       we DONT want overriden English phrase to overwrite default German
					foreach ($this->loaded[$lid][$g] as $phr_id => $phr) {
						if ($loaded_phrases !== null && isset($loaded_phrases[$phr_id])) {
							continue;
						}

						$phrases[$phr_id] = $phr;
					}
				}
			}
		}

		return $phrases;
	}

	public function load($groups, $language, array $loaded_phrases = null)
	{
		if ($this->loaded !== null) {
			return $this->returnPhrases($groups, $language, $loaded_phrases);
		}

		$q = $this->dbconn->query("
			SELECT language_id, groupname, name, COALESCE(NULLIF(phrase, ''), original_phrase) AS phrase
			FROM phrases
		");

		$this->loaded = array();
		while ($r = $q->fetch()) {
			if (!isset($this->loaded[$r['language_id']])) {
				$this->loaded[$r['language_id']] = array();
			}
			if (!isset($this->loaded[$r['language_id']][$r['groupname']])) {
				$this->loaded[$r['language_id']][$r['groupname']] = array();
			}

			$this->loaded[$r['language_id']][$r['groupname']][$r['name']] = $r['phrase'];
		}

		return $this->returnPhrases($groups, $language, $loaded_phrases);
	}
}
