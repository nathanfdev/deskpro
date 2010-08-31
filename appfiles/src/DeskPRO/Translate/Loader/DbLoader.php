<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Translate
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Translate\Laoder;

/**
 * Loads phrases from the database
 */
class DbLoader implements LoaderInterface
{
	/**
	 * Plain database connection for raw queries
	 * @var DeskPRO\DBAL\Connection
	 */
	protected $dbconn;

	/**
	 * @var array
	 */
	protected $language_ids;



	/**
	 * @param array $language_ids Array of lang ids from most specific (child) to most generic (parents)
	 * @param \DeskPRO\DBAL\Connection $dbconn
	 */
	public function __construct(array $language_ids, \DeskPRO\DBAL\Connection $dbconn)
	{
		$this->dbconn = $dbconn;
	}



	public function load($groups)
	{
		$group_in = "'" . implode("','", $groups) . "'";
		$lang_in = implode(',', $this->language_ids);

		$phrases = $this->dbconn->fetchAll("
			SELECT DISTINCT name, phrase
			FROM phrases
			WHERE language_id = IN ($lang_in) AND group IN ($group_in)
			ORDER BY language_id DESC
		");

		return $phrases;
	}
}