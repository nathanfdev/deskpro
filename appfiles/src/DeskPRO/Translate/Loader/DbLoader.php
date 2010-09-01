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

namespace DeskPRO\Translate\Loader;

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
	protected $language_ids = array();



	/**
	 * @param \DeskPRO\DBAL\Connection $dbconn
	 */
	public function __construct(\DeskPRO\DBAL\Connection $dbconn)
	{
		$this->dbconn = $dbconn;
	}
	
	
	
	/**
	 * Set the language ID's to fetch from.
	 * 
	 * @param array $language_ids 
	 */
	public function setLanguageIds(array $language_ids)
	{
		$this->language_ids = $language_ids;
	}



	public function load($groups)
	{
		$group_in = "'" . implode("','", $groups) . "'";
		
		// 0 contains non-language language like cat names and such
		$langs = $this->language_ids;
		$langs[] = 0;
		$lang_in = implode(',', $langs);

		$phrases = $this->dbconn->fetchAll("
			SELECT DISTINCT name, phrase
			FROM phrases
			WHERE language_id IN ($lang_in) AND groupname IN ($group_in)
			ORDER BY language_id DESC
		");

		return $phrases;
	}
}