<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Translate
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Translate\Loader;

/**
 * Loads phrases from the database
 */
class DbLoader implements LoaderInterface
{
	/**
	 * Plain database connection for raw queries
	 * @var Application\DeskPRO\DBAL\Connection
	 */
	protected $dbconn;

	/**
	 * @param \Application\DeskPRO\DBAL\Connection $dbconn
	 */
	public function __construct(\Application\DeskPRO\DBAL\Connection $dbconn)
	{
		$this->dbconn = $dbconn;
	}

	public function load($groups, $locale)
	{
		// No locale means we have nothing to do here,
		// usually means we're in an area without db yet (pre install?)
		if (!$locale OR !$locale['id']) {
			return array();
		}

		$group_in = "'" . implode("','", $groups) . "'";

		$langs = $locale->getAllParentIds();
		$langs[] = $locale['id'];

		// null contains non-language language like cat names and such
		$langs[] = 'NULL';

		$lang_in = implode(',', $langs);

		// Note that ordering by lang id here is an easy way to give child phrases
		// priority over parent phrases. Children are always created after parents, therefore
		// their ID's are always higher.

		$q = $this->dbconn->query("
			SELECT name, phrase, groupname
			FROM phrases
			WHERE language_id IN ($lang_in) AND groupname IN ($group_in)
			GROUP BY name
			ORDER language_id DESC
		");

		$phrases = array();
		while ($r = $q->fetch()) {
			if (!isset($phrases[$r['groupname']])) $phrases[$r['groupname']] = array();

			$phrases[$r['groupname']][$r['name']] = $r['phrase'];
		}

		return $phrases;
	}
}