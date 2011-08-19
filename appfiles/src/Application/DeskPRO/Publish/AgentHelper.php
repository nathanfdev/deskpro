<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Addons
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Publish;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;

use Application\DeskPRO\Searcher\ArticleSearch;

use Orb\Util\Arrays;

/**
 * Helps fetch info related to structure of Publish
 */
class AgentHelper implements PersonContextInterface
{
	const ARTICLES  = 'articles';
	const DOWNLOADS = 'downloads';
	const NEWS      = 'news';

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}


	/**
	 * Get the category structure
	 * 
	 * @param string $type
	 * @return array
	 */
	public function getCategoryStructure($type, $flat = false)
	{
		// TODO cacheable
		$entity_name = self::getCatEntityNameFor($type);

		return App::getEntityRepository($entity_name)->getRootNodes();
	}


	/**
	 * @param string $type
	 * @return int
	 */
	public function getCategoryCounts($type)
	{
		$cache = App::getCache('agent_publish_structure');
		$cache_id = "counts_$type";

		if (($counts = $cache->load($cache_id)) === false) {

			$entity_name = self::getCatEntityNameFor($type);
			$repos = App::getEntityRepository($entity_name);
			$counts = $repos->getAllCounts($this->person_context, null);

			$cache->save($counts, $cache_id);
		}

		return $counts;
	}


	/**
	 * Get an array of glossary words, sorted into an alphabetically-indexed array
	 *
	 * @return array
	 */
	public function getGlossaryWordsIndex()
	{
		$cache = App::getCache('agent_publish_structure');
		$cache_id = "glossary_words";

		if (($glossary_words = $cache->load($cache_id)) === false) {
			$glossary_words = App::getEntityRepository('DeskPRO:GlossaryWord')->getWords();
			$glossary_words = Arrays::sortIntoAlphabeticalIndex($glossary_words, null, true, true);

			$cache->save($glossary_words, $cache_id);
		}

		return $glossary_words;
	}

	
	/**
	 * Get the content entity for a publish type
	 *
	 * @static
	 * @throws \InvalidArgumentException
	 * @param $type
	 * @return string
	 */
	public static function getEntityNameFor($type)
	{
		switch ($type) {
			case self::ARTICLES:
				return 'DeskPRO:Article';
				break;
			case self::DOWNLOADS:
				return 'DeskPRO:Download';
				break;
			case self::NEWS:
				return 'DeskPRO:News';
				break;
		}

		throw new \InvalidArgumentException("Unknow type `$type`");
	}


	/**
	 * Get the category entity for a publish type
	 *
	 * @static
	 * @throws \InvalidArgumentException
	 * @param $type
	 * @return string
	 */
	public static function getCatEntityNameFor($type)
	{
		switch ($type) {
			case self::ARTICLES:
				return 'DeskPRO:ArticleCategory';
				break;
			case self::DOWNLOADS:
				return 'DeskPRO:DownloadCategory';
				break;
			case self::NEWS:
				return 'DeskPRO:NewsCategory';
				break;
		}

		throw new \InvalidArgumentException("Unknow type `$type`");
	}
}