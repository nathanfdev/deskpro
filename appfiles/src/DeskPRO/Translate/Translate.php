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

namespace DeskPRO\Translate;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \DeskPRO\Translate\Loader\LoaderInterface;

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
	 * The phrases loaded so far
	 * @var array
	 */
	protected $phrases = array();

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
	 * @param LoaderInterface $loader A loader that'll load phrases from somehwere
	 */
	public function __construct(LoaderInterface $loader)
	{
		$this->loader = $loader;
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

		$this->phrases = array_merge($this->phrases, $this->loader->load($this->_pending_groups));

		$this->_loaded_groups = array_merge($this->_loaded_groups, $this->_pending_groups);
		$this->_pending_groups = array();
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
		if (!$phrase_name) return '';

		if (!isset($this->phrases[$phrase_name])) {
			$check_group = $this->getPhraseGroupFromName($phrase_name);

			if (!in_array($check_group, $this->_loaded_groups)) {
				$this->_pending_groups[] = $check_group;

				$this->_loadPendingPhraseGroups();
				return $this->getPhraseText($phrase_name);
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

		return call_user_func_array('Orb\\Util\\Strings::format', $args);
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