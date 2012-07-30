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
 * @category Translate
 */

namespace Application\DeskPRO\Translate\Loader;

/**
 * Loads phrases from the database
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
	protected $loaded_langs = array();

	/**
	 * @param \Application\DeskPRO\DBAL\Connection $dbconn
	 */
	public function __construct(\Application\DeskPRO\DBAL\Connection $dbconn)
	{
		$this->dbconn = $dbconn;
	}

	public function load($groups, $language)
	{
		// No lang means we have nothing to do here,
		// usually means we're in an area without db yet (pre install?)
		if (!$language OR !$language['id']) {
			return array();
		}

		// The LoaderInterface expects to load groups as they're needed,
		// but thats expensive in the db so we load then entire thing in one query
		// - This check prevents the query from re-running when another call is made
		if (isset($this->loaded_langs[$language['id']])) {
			return $this->loaded_langs[$language['id']];
		}

		$this->loaded_langs[$language['id']] = true;

		$langs = array();
		$langs[] = 1; // default deskpro lang

		if ($language) {
			$langs[] = $language->getId(); // the chosen lang
		}

		// null contains non-language language like cat names and such
		$langs[] = '0';

		$langs = array_unique($langs, \SORT_STRING);

		$lang_in = implode(',', $langs);

		// Note that ordering by lang id here is an easy way to give child phrases
		// priority over parent phrases. Children are always created after parents, therefore
		// their ID's are always higher.

		// Depending on the interface, we load user, user+agent or user+agent+admin
		if (DP_INTERFACE == 'admin') {
			$group_like = '1';
		} elseif (DP_INTERFACE == 'agent') {
			$group_like = 'groupname LIKE "agent.%" OR groupname LIKE "user.%" OR groupname LIKE "obj_%"';
		} else {
			$group_like = 'groupname LIKE "user.%" OR groupname LIKE "obj_%"';
		}

		$q = $this->dbconn->query("
			SELECT name, phrase, original_phrase
			FROM phrases
			WHERE language_id IN ($lang_in) AND ($group_like)
			GROUP BY name
			ORDER BY language_id DESC
		");

		$phrases = array();
		while ($r = $q->fetch()) {

			$phrase_text = $r['phrase'];
			if (empty($phrase_text)) {
				$phrase_text = $r['original_phrase'];
			}

			$phrases[$r['name']] = $phrase_text;
		}

		$this->loaded_langs[$language['id']] = $phrases;

		return $phrases;
	}
}
