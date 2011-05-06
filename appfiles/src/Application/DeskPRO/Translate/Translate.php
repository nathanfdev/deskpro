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

namespace Application\DeskPRO\Translate;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

use Application\DeskPRO\Translate\Loader\LoaderInterface;
use Application\DeskPRO\Entity\Locale as LocaleEntity;

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
class Translate
{
	/**
	 * The phrases loaded so far
	 * @var array
	 */
	protected $_phrases = array();

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
	 * An array of id=>entity of locales we've handled so far
	 * @var \Application\DeskPRO\Entity\Locale[]
	 */
	protected $_loaded_locales = array();

	/**
	 * The default locale, used when calling setLocale with no argument
	 * This is the first locale set
	 * @var \Application\DeskPRO\Entity\Locale
	 */
	protected $_default_locale = null;

	/**
	 * Set the locale we're using right now by default
	 * @var \Application\DeskPRO\Entity\Locale
	 */
	protected $_locale = null;

	/**
	 * See getCountPhraseSelector()
	 * @var \Symfony\Component\Translation\MessageSelector
	 */
	protected $_phrase_selector = null;

	/**
	 * @var \Application\DeskPRO\Translate\ObjectPhraseNamer
	 */
	protected $_phrase_object_namer = null;



	/**
	 * @param string $locale The default locale to use
	 * @param LoaderInterface $loader A loader that'll load phrases from somehwere
	 */
	public function __construct(LoaderInterface $loader)
	{
		$this->setLocale(SystemLocale::getInstance(), false);
		$this->loader = $loader;
	}


	/**
	 * Set the default locale to used when fetching phrases. You can override this in phrase(),
	 * so setting the default here just makes those calls cleaner.
	 *
	 * $load_previous_groups means that all the phrase groups loaded so far are loaded for this
	 * new locale. Thought being that this group will probably need the same phrases as the other.
	 *
	 * @param LocaleEntity $locale
	 * @param bool $load_previous_groups
	 * @return void
	 */
	public function setLocale(LocaleEntity $locale = null, $load_previous_groups = true)
	{
		// If this is the first locale, we'll consider it the "default"
		if ($locale AND $this->_locale === null) {
			$this->_default_locale = $locale;
		}

		if (!$locale) {
			$locale = $this->_default_locale;
		}

		$last_id = null;
		if ($this->_locale) {
			$last_id = $this->_locale['id'];
		}

		$this->_locale = $locale;
		$this->_loaded_locales[$locale['id']] = $locale;

		if ($last_id AND $load_previous_groups AND isset($this->_loaded_groups[$last_id])) {
			$this->loadPhraseGroups($this->_loaded_groups[$last_id], $locale);
		}
	}


	/**
	 * Temporarily resets the locale to $locale and runs $func, and then
	 * resets the locale after.
	 *
	 * This will attempt to catch exceptions so the locale is always reset
	 * afterwards.
	 *
	 * @param LocaleEntity $locale
	 * @param callback     $func
	 */
	public function setTemporaryLocale(LocaleEntity $locale, $func)
	{
		$this->setLocale($locale);

		$e = null;
		try {
			$func($this, $locale);
		} catch (\Exception $e) {}

		$this->setLocale();

		if ($e) {
			throw $e;
		}
	}


	/**
	 * Set the default locale. This just makes it easier to switch "back" to it when
	 * using setLocale(null).
	 */
	public function setDefaultLocale(LocaleEntity $locale)
	{
		$this->_default_locale = $locale;
	}



	/**
	 * Get the currently set locale.
	 *
	 * @return \Application\DeskPRO\Entity\Locale
	 */
	public function getLocale()
	{
		return $this->_locale;
	}



	/**
	 * Add a group of phrases we want to load.
	 *
	 * @param  $group
	 */
	public function loadPhraseGroups($group, $locale)
	{
		$locale_id = $locale['id'];
		if (!isset($this->_pending_groups[$locale_id])) $this->_pending_groups[$locale_id] = array();

		for ($i = 0, $max = func_num_args(); $i < $max; $i++) {
			$group = func_get_arg($i);
			if (!in_array($group, $this->_loaded_groups)) {
				$this->_pending_groups[$locale_id][] = $group;
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

		foreach ($this->_pending_groups as $locale_id => $groups) {
			$groups = array_unique($groups);
			$groups = Arrays::removeFalsey($groups);

			if (!isset($this->_loaded_locales[$locale_id])) {
				$this->_loaded_locales[$locale_id] = App::getEntityRepository('DeskPRO:Locale')->find($locale_id);
			}
			$locale = $this->_loaded_locales[$locale_id];

			if (!isset($this->_phrases[$locale_id])) $this->_phrases[$locale_id] = array();
			$this->_phrases[$locale_id] = array_merge($this->_phrases[$locale_id], $this->loader->load($groups, $locale));

			if (!isset($this->_loaded_groups[$locale_id])) $this->_loaded_groups[$locale_id] = array();
			$this->_loaded_groups[$locale_id] = array_merge($this->_loaded_groups[$locale_id], $groups);
		}

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
	 * @param  Locale|int $locale The Locale entity to use, or its id
	 * @return string
	 */
	public function getPhraseText($phrase_name, $locale = null)
	{
		if ($locale === null) $locale = $this->_locale;
		if (!$phrase_name) return '';

		if (Numbers::isInteger($locale)) {
			$locale_id = $locale;
		} else {
			$locale_id = $locale['id'];
		}

		if (!isset($this->_phrases[$locale_id][$phrase_name])) {
			$check_group = $this->getPhraseGroupFromName($phrase_name);

			if (!isset($this->_loaded_groups[$locale_id]) OR !in_array($check_group, $this->_loaded_groups[$locale_id])) {
				if (!isset($this->_pending_groups[$locale_id])) $this->_pending_groups[$locale_id] = array();
				$this->_pending_groups[$locale_id][] = $check_group;

				$this->_loadPendingPhraseGroups();
				return $this->getPhraseText($phrase_name, $locale_id);
			}

			return null;
		}

		return $this->_phrases[$locale_id][$phrase_name];
	}



	/**
	 * Same as getPhraseText, except we run the phrase through the selector
	 * to fetch the correct plural phrase for the given $count.
	 *
	 * @param  string $phrase_name The phrase you want to fetch
	 * @param  int $count The count
	 * @param  Locale|int $locale The Locale entity to use, or its id
	 * @return string
	 */
	public function getPhraseTextCount($phrase_name, $count, $locale)
	{
		$phrase_text = $this->getPhraseText($phrase_name, $count, $locale);
		if (!$phrase_text) {
			return null;
		}

		if ($locale === null) {
			$locale = $this->_locale;
		} elseif (Numbers::isInteger($locale)) {
			$local = $this->_loaded_locales[$locale];
		}

		$locale_code = $locale['locale'];

		return $this->getCountPhraseSelector()->choose($phrase_text, $count, $locale);
	}



	/**
	 * Get a phrase from a compatible object that knows how to describe a phrase ID.
	 *
	 * $property may be null, in which case it's expected to be a 'title' or 'name',
	 * or the only item on the object that is translatable.
	 *
	 * @param stdObject $object The object to get a phrase for
	 * @param string $property A specific thing in the object to translate
	 * @param  Locale|int $locale The Locale entity to use, or its id
	 * @return string
	 */
	public function getPhraseObject($object, $property = null, $locale = null)
	{
		#------------------------------
		# Standard translation interfaces
		#------------------------------

		if ($object instanceof DelegatePhraseInterface) {
			return $object->getPhrase($translator, $locale);

		} else if ($object instanceof HasPhraseName) {
			$phrase_name = $object->getPhraseName($property);
			if ($phrase_name) {
				return $this->phrase($phrase_name, $locale);
			} else {
				return $object->getPhraseDefault($property);
			}
		}

		#------------------------------
		# Phrase namer inspects objects..
		#------------------------------

		$namer = $this->getObjectPhraseNamer();
		$phrase_name = $namer->getPhraseName($object, $property);
		if ($phrase_name) {
			return $this->phrase($phrase_name, $locale);
		} else {
			$phrase_text = $this->getObjectPhraseNamer()->getPhraseDefault($object, $property);
			if ($phrase_text !== null) {
				return $phrase_text;
			}
		}

		// No phrase
		return '';
	}



	/**
	 * Fetch a phrase from the currently set locale, and insert the passed variables into the placeholders.
	 *
	 * If $vars contains a 'count' value, then the phrase is expected to be a pluralized and will be passed
	 * through the message selector.
	 *
	 * If $phrase_name is an object, it should be a compatible phrase object (see ObjectPhraseNamer).
	 * If it's an array, the first item should be an object and the second a string, which is used as the
	 * 'property'.
	 *
	 * <code>
	 * echo $translate->phrase('core.welcome_back_x', array('name' => 'Christopher'));
	 * </code>
	 *
	 * @param  string $phrase_name  The phrase to fetch
	 * @param  array  $vars         Variables to place into the phrase
	 * @param  Locale|int $locale The Locale entity to use, or its id
	 * @return string
	 */
	public function phrase($phrase_name, array $vars = array(), $locale = null)
	{
		if (is_object($phrase_name) OR (is_array($phrase_name) AND is_object($phrase_name[0]))) {
			if (is_array($phrase_name)) {
				list ($object, $property) = $phrase_name;
			} else {
				$object = $phrase_name;
				$property = null;
			}

			$phrase_text = $this->getPhraseObject($object, $property, $locale);
		} elseif (isset($vars['count'])) {
			$phrase_text = $this->getPhraseTextCount($phrase_name, $vars['count'], $locale);
		} else {
			$phrase_text = $this->getPhraseText($phrase_name, $locale);
		}

		if (!$phrase_text) $phrase_text = '';

		if ($vars) {
			$keys = array_keys($vars);
			$values = array_values($vars);

			array_walk($keys, function (&$val) {
				$val = '{{' . $val . '}}';
			});

			$vars = array_combine($keys, $values);
		}

		$phrase_text = strtr($phrase_text, $vars);

		// A second pass detects phrase. replacements that might've been put in by replacements themselves
		$m = null;
		if (preg_match_all('#{{phrase\.([a-zA-Z0-9\-_\.]+)}}#', $phrase_text, $m)) {
			foreach ($m[1] as $sub_phrase_name) {
				if ($sub_phrase_name == $phrase_name) continue; //prevent loops
				$sub_phrase_text = $this->phrase($sub_phrase_name, $vars, $locale);
				$phrase_text = str_replace("{{phrase.$sub_phrase_name}}", $sub_phrase_text, $phrase_text);
			}
		}

		return $phrase_text;
	}



	/**
	 * @return \Symfony\Component\Translation\MessageSelector
	 */
	public function getCountPhraseSelector()
	{
		if ($this->_phrase_selector !== null) return $this->_phrase_selector;

		$this->_phrase_selector = new \Symfony\Component\Translation\MessageSelector();
	}

	/**
	 * @return \Application\DeskPRO\Translate\ObjectPhraseNamer
	 */
	public function getObjectPhraseNamer()
	{
		if ($this->_phrase_object_namer !== null) return $this->_phrase_object_namer;

		$this->_phrase_object_namer = new \Application\DeskPRO\Translate\ObjectPhraseNamer();

		return $this->_phrase_object_namer;
	}
}