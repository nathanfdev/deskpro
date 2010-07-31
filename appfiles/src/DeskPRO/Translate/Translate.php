<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Translate;

use Symfony\Components\DependencyInjection\ContainerInterface;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use DeskPRO\Entities;

/**
 * This class is responsible for loading phrases from a language stored in the database.
 *
 * <code>
 * $t = new Translate($language, $container);
 * $t->loadPhraseGroups('core', 'profile', 'tickets');
 * echo $t['tickets.ask_a_question'];
 * echo $t->phrase('core.welcome_back_x', 'Christopher');  
 * </code>
 *
 * @see Language
 * @see Phrase
 */
class Translate implements \ArrayAccess
{
	/**
	 * The database connection we'll use to fetch templates. Not using ORM, faster
	 * to fetch with pure sql.
	 *
	 * @var Doctrine\DBAL\Connection
	 */
	protected $dbconn;

	/**
	 * The currently selected language
	 * @var DeskPRO\Entities\Language
	 */
	protected $language;

	/**
	 * The phrases loaded so far
	 * @var array
	 */
	protected $phrases;

	/**
	 * An array of groups that we need to load in the next batch
	 * @var array
	 */
	protected $_pending_groups = array();

	/**
	 * An array of groups we've already loaded
	 * @var array
	 */
	protected $_loaded_groups = array();



	/**
	 * @param Language $language The language we're using
	 * @param ContainerInterface $container The DI container we'll use to get the database connection
	 */
	public function __construct(Language $language, ContainerInterface $container)
	{
		$this->language = $language;
		$this->dbconn = $container->getService('database_connection');
	}



	/**
	 * Get the language used.
	 *
	 * @return DeskPRO\Entities\Language
	 */
	public function getLanguage()
	{
		return $this->language;
	}



	/**
	 * Add a group of phrases we want to load.
	 *
	 * @param  $group
	 */
	public function loadPhraseGroups($group)
	{
		for ($i = 0, $max = func_num_args(); $i < $max; $i++) {
			$group = func_get_arg($i);
			if (!in_array($group, $this->_loaded_groups)) {
				$this->pending_load[] = $group;
			}
		}
	}


	/**
	 * When an unknown phrase is encountered in a group we haven't loaded yet,
	 * we'll load all pending phrase groups.
	 */
	protected function _loadPendingPhraseGroups()
	{
		if (!$this->_pending_groups) {
			return;
		}

		$this->_pending_groups = array_unique($this->_pending_groups);
		$this->_pending_groups = Arrays::removeFalsey($this->_pending_groups);

		$group_in = "'" . implode("','", $this->_pending_groups);

		// TODO handle language hierarchy
		$phrases = $this->dbconn->fetchAll("
			SELECT name, phrase
			FROM phrases
			WHERE language_id = ? AND group IN ($group_in)
		", array($this->style['id']));

		$this->phrases = array_merge($phrases, $this->phrases);
	}



	/**
	 * Get the phrase group from the name of a phrase. The phrase "deskpro.example_phrase"
	 * has the group named "deskpro".
	 *
	 * @param  string $phrase_name The name of the phrase
	 * @return string
	 */
	public function getPhraseGroupFromName($phrase_name)
	{
		$pos = strpos($phrase_name, '.');
		if ($pos === false) {
			return false;
		}

		return substr($phrase_name, 0, $pos);
	}


	/**
	 * Get the phrase text for a given name.
	 *
	 * @param  string $phrase_name The phrase you want to fetch
	 * @return string
	 */
	public function getPhraseText($phrase_name)
	{
		if (!isset($this->phrases[$phrase_name])) {
			if ($this->_pending_groups) {
				$check_group = $this->getPhraseGroupFromName($phrase_name);
				if (in_array($check_group, $this->_pending_groups)) {
					$this->_loadPendingPhraseGroups();
					return $this->getPhrase($phrase_name);
				}
			}

			return null;
		}

		return $this->phrases[$phrase_name];
	}



	/**
	 * Fetch a phrase and insert the passed variables into the placeholders.
	 *
	 * <code>
	 * echo $translate->phrase('core.welcome_back_x', 'Christopher');
	 * </code>
	 *
	 * @param  string $phrase_name  The phrase to fetch
	 * @param  string $var...       Variables to interpolate into the phrase
	 * @return string
	 */
	public function phrase($phrase_name)
	{
		$phrase_text = $this->getPhraseText($phrase_name);
		if (!$phrase_text) $phrase_text = '';

		// no args to replace, so just return text
		if (func_num_args() == 1) {
			return $phrase_text;
		}

		$args = func_get_args();
		$args[0] = $phrase_text;

		return call_user_func_array('DeskPRO\\Util\\Strings::format', $args);
	}



	public function offsetExists($offset)
	{
		return $this->getPhraseText($offset) !== null;
	}

	public function offsetSet($offset, $value)
	{
		throw new BadMethodCallException('You cannot set phrases');
	}

	public function offsetGet($offset)
	{
		return $this->getPhraseText($offset);
	}

	public function offsetUnset($offset)
	{
		throw new BadMethodCallException('You cannot unset phrases');
	}
}