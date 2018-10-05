<?php

/**
 * DeskPRO.
 *
 * @category Translate
 */

namespace Application\DeskPRO\Translate;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Language as LanguageEntity;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EventDispatcher\DataEvent;
use Application\DeskPRO\HttpFoundation\Session;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Translate\Loader\LoaderInterface;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This class is responsible for loading phrases from a language stored in the database.
 *
 * <code>
 * $t = new Translate($language, $container);
 * $t->loadPhraseGroups(array('core', 'profile', 'tickets'));
 * echo $t['tickets.ask_a_question'];
 * echo $t->phrase('core.welcome_back_x', 'Christopher');
 * </code>
 *
 * @see Language
 * @see Phrase
 */
class Translate implements PersonContextInterface, TranslatorInterface
{
    const EVENT_NO_PHRASE = 'DeskPRO_onTranslateNoPhrase';

    /**
     * The phrases loaded so far.
     *
     * @var array
     */
    protected $_phrases = [];

    /**
     * An array of groups that we need to load in the next batch.
     *
     * @var array
     */
    protected $_pending_groups = [];

    /**
     * An array of groups we've already loaded.
     *
     * @var array
     */
    protected $_loaded_groups = [];

    /**
     * An array of id=>entity of languages we've handled so far.
     *
     * @var \Application\DeskPRO\Entity\Language[]
     */
    protected $_loaded_languages = [];

    /**
     * The default language, used when calling setLanguage with no argument
     * This is the first language set.
     *
     * @var \Application\DeskPRO\Entity\Language
     */
    protected $_default_language = null;

    /**
     * Set the language we're using right now by default.
     *
     * @var \Application\DeskPRO\Entity\Language
     */
    protected $_language = null;

    /**
     * @var ObjectPhraseNamer
     */
    protected $_phrase_object_namer = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $_person_context;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $_default_person_context;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $_event_dispatcher = null;

    /**
     * @var array
     */
    protected static $_missing_phrases = [];

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @param LoaderInterface          $loader
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(LoaderInterface $loader, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->setLanguage(SystemLanguage::getInstance(), false);
        $this->loader = $loader;

        $this->_event_dispatcher = $eventDispatcher;
    }

    /**
     * @param Session $session
     */
    public function setSession(Session $session = null)
    {
        if (!$session || !$session->isStarted()) {
            return;
        }

        $this->setLanguage($session->getLanguage());
        $this->setDefaultLanguage($session->getLanguage());
    }

    /**
     * Set the current person context. This will also change the language to their preference.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    public function setPersonContext(Person $person = null, $load_previous_groups = true)
    {
        if (!$person) {
            $person = $this->_default_person_context;
        }

        $this->_person_context = $person;

        if (!$this->_default_person_context) {
            $this->_default_person_context = $person;
        }

        if ($this->_language['id'] != $this->_person_context['id']) {
            $this->setLanguage($person->getLanguage(), $load_previous_groups);
        }
    }

    /**
     * Temporarily resets the language to $language and runs $func, and then
     * resets the language after.
     *
     * This will attempt to catch exceptions so the language is always reset
     * afterwards.
     *
     * @param Person   $person
     * @param callback $func
     *
     * @throws \Exception
     *
     * @internal param LanguageEntity $language
     */
    public function setTemporaryPersonContext(Person $person, $func)
    {
        $this->setPersonContext($person);

        $e = null;
        try {
            $func($this, $person->getLanguage());
        } finally {
            $this->setPersonContext();
        }
    }

    /**
     * Call $func with the language set to $person. When the call is done,
     * the language is reset back to default.
     *
     * @param Person $person
     * @param        $func
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function callWithPersonContext(Person $person, $func)
    {
        $this->setPersonContext($person);

        try {
            return $func($this, $person->getLanguage());
        } finally {
            $this->setPersonContext();
        }
    }

    /**
     * Reset to the default person context.
     */
    public function resetToDefaultPersonContext()
    {
        $this->setPersonContext(null);
    }

    /**
     * Sets the default person context.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    public function setDefaultPersonContext(Person $person)
    {
        $this->_default_person_context = $person;
    }

    /**
     * Get the current person context.
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPersonContext()
    {
        return $this->_person_context;
    }

    /**
     * Set the default language to used when fetching phrases. You can override this in phrase(),
     * so setting the default here just makes those calls cleaner.
     *
     * $load_previous_groups means that all the phrase groups loaded so far are loaded for this
     * new language. Thought being that this group will probably need the same phrases as the other.
     *
     * @param LanguageEntity $language
     * @param bool           $load_previous_groups
     */
    public function setLanguage(LanguageEntity $language = null, $load_previous_groups = true)
    {
        // If this is the first language, we'll consider it the "default"
        if ($language and $this->_language === null) {
            $this->_default_language = $language;
        }

        if (!$language) {
            $language = $this->_default_language;
        }

        $lastId = null;
        if ($this->_language) {
            $lastId = $this->_language['id'];
        }

        $this->_language                          = $language;
        $this->_loaded_languages[$language['id']] = $language;

        if ($lastId and $load_previous_groups and isset($this->_loaded_groups[$lastId])) {
            $this->loadPhraseGroups($this->_loaded_groups[$lastId], $language);
        }
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $language = $this->localeToLanguage($locale);
        if ($language) {
            $this->setLanguage($language);
        }
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getLanguage()->getLocale();
    }

    /**
     * Resets the current language to the default.
     *
     * This is an alias of setLanguage(null)
     */
    public function resetToDefaultLanguage()
    {
        $this->setLanguage(null);

        return;
    }

    /**
     * Temporarily resets the language to $language and runs $func, and then
     * resets the language after.
     *
     * This will attempt to catch exceptions so the language is always reset
     * afterwards.
     *
     * @param LanguageEntity $language
     * @param callback       $func
     *
     * @throws null|\Exception
     */
    public function setTemporaryLanguage(LanguageEntity $language = null, $func)
    {
        $this->setLanguage($language);

        $e = null;
        try {
            $func($this, $language);
        } catch (\Exception $e) {
        }

        $this->setLanguage();

        if ($e) {
            throw $e;
        }
    }

    /**
     * Call a function with the language set ot $language. After the call,
     * the language is reset back to the default.
     *
     * @param Language $language
     * @param          $func
     *
     * @throws null|\Exception
     *
     * @return mixed
     */
    public function callWithLanguage(LanguageEntity $language = null, $func)
    {
        $this->setLanguage($language);

        $e   = null;
        $ret = null;
        try {
            $ret = $func($this, $language);
        } catch (\Exception $e) {
        }

        $this->setLanguage();

        if ($e) {
            throw $e;
        }

        return $ret;
    }

    /**
     * Set the default language. This just makes it easier to switch "back" to it when
     * using setLanguage(null).
     *
     * @param LanguageEntity $language
     */
    public function setDefaultLanguage(LanguageEntity $language)
    {
        $this->_default_language = $language;
    }

    /**
     * Get the currently set language.
     *
     * @return \Application\DeskPRO\Entity\Language
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * Add a group of phrases we want to load.
     *
     * @param                $groups
     * @param LanguageEntity $language
     *
     * @internal param $group
     */
    public function loadPhraseGroups($groups, LanguageEntity $language = null)
    {
        if (!is_array($groups)) {
            $groups = [$groups];
        }

        if (!$language) {
            $language = $this->_language;
        }

        $languageId = $language->getId();

        if (!isset($this->_pending_groups[$languageId])) {
            $this->_pending_groups[$languageId] = [];
        }

        foreach ($groups as $group) {
            if (!in_array($group, $this->_loaded_groups)) {
                $this->_pending_groups[$languageId][] = $group;
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

        foreach ($this->_pending_groups as $languageId => $groups) {
            $groups = array_unique($groups);
            $groups = Arrays::removeFalsey($groups);

            if (!isset($this->_loaded_languages[$languageId])) {
                $this->_loaded_languages[$languageId] = App::getEntityRepository(Language::class)->find(
                    $languageId
                );
            }
            $language = $this->_loaded_languages[$languageId];

            if (!isset($this->_phrases[$languageId])) {
                $this->_phrases[$languageId] = [];
            }
            $this->_phrases[$languageId] = array_merge(
                $this->_phrases[$languageId],
                $this->loader->load($groups, $language)
            );

            if (!isset($this->_loaded_groups[$languageId])) {
                $this->_loaded_groups[$languageId] = [];
            }
            $this->_loaded_groups[$languageId] = array_merge($this->_loaded_groups[$languageId], $groups);
        }

        $this->_pending_groups = [];
    }

    /**
     * Get the phrase group from the name of a phrase. The phrase "deskpro.example_phrase"
     * has the group named "deskpro".
     *
     * @param string $phraseName The name of the phrase
     *
     * @return string
     */
    public function getPhraseGroupFromName($phraseName)
    {
        if (!is_string($phraseName)) {
            return false;
        }

        $pos = strpos($phraseName, '.');
        if ($pos === false) {
            return '__default__';
        }

        if ($phraseName[0] == '/') {
            $phraseName = substr($phraseName, 1);
            $phraseName = str_replace('\.', '.', $phraseName);
        }

        $parts = explode('.', $phraseName);

        // foo.bar         => foo
        // foo.bar.baz     => foo.bar
        // foo.bar.baz.hoo => foo.bar
        $name = $parts[0];
        if (isset($parts[2])) {
            $name .= '.'.$parts[1];
        }

        return $name;
    }

    /**
     * Get the phrase text for a given name.
     *
     * @param string       $phraseName     The phrase you want to fetch
     * @param Language|int $language       The Language entity to use, or its id
     * @param bool         $nullOnNotFound Should return null if phrase not found or not
     *
     * @return string|void
     */
    public function getPhraseText($phraseName, $language = null, $nullOnNotFound = false)
    {
        if ($language === null) {
            $language = $this->_language;
        }
        if (!$phraseName) {
            return '';
        }
        if (!is_string($phraseName)) {
            return '('.gettype($phraseName).')';
        }

        if (Numbers::isInteger($language)) {
            $languageId = $language;
        } else {
            $languageId = $language->getId();
        }

        if (!isset($this->_phrases[$languageId][$phraseName])) {
            $checkGroup = $this->getPhraseGroupFromName($phraseName);
            if (!$checkGroup) {
                if ($nullOnNotFound) {
                    return;
                }

                return $this->_noPhrase($phraseName, $language);
            }

            if (!isset($this->_loaded_groups[$languageId]) or !in_array(
                    $checkGroup,
                    $this->_loaded_groups[$languageId]
                )
            ) {
                if (!isset($this->_pending_groups[$languageId])) {
                    $this->_pending_groups[$languageId] = [];
                }
                $this->_pending_groups[$languageId][] = $checkGroup;

                $this->_loadPendingPhraseGroups();

                return $this->getPhraseText($phraseName, $languageId, $nullOnNotFound);
            }

            if ($nullOnNotFound) {
                return;
            }

            return $this->_noPhrase($phraseName, $language);
        }

        return $this->_phrases[$languageId][$phraseName];
    }

    public static function reportMissingPhrases()
    {
        if (count(self::$_missing_phrases)) {
            try {
                $logger  = App::createNewLogger('missing_phrase_logger', null);
                $message = "'The following phrases are missing:\n";

                foreach (self::$_missing_phrases as $phrase) {
                    $message .= "{$phrase}\n";
                }

                $logger->log(
                    $message,
                    'WARN',
                    [
                        'subject' => '[DeskPRO Missing Phrases]',
                        'message' => $message,
                    ]
                );
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param array $phrase_names
     * @param null  $language
     *
     * @return array
     */
    public function getArrayPhraseTexts(array $phrase_names, $language = null)
    {
        if ($language === null) {
            $language = $this->_language;
        }

        if (Numbers::isInteger($language)) {
            $languageId = $language;
        } else {
            $languageId = $language['id'];
        }

        if ($languageId !== $this->_language->getId()) {
            $language = $this->_loaded_languages[$languageId] = App::getEntityRepository(Language::class)->find(
                $languageId
            );
        }

        $preloadGroups = [];

        $starPatterns  = [];
        $regexPatterns = [];
        $phraseIds     = [];

        foreach ($phrase_names as $phraseName) {
            $preloadGroups[] = $this->getPhraseGroupFromName($phraseName);

            // A regex pattern like /admin\.general\.default.*?/
            if ($phraseName[0] == '/' && substr($phraseName, -1, 1) == '/') {
                $regexPatterns[] = $phraseName;

                // A simplified star pattern like admin.general.default*
            } elseif (strpos($phraseName, '*') !== false) {
                $starPatterns[] = $phraseName;

                // A fully-qualified phrase name
            } else {
                $phraseIds[] = $phraseName;
            }
        }

        $this->loadPhraseGroups($preloadGroups, $language);

        $phraseTexts = [];
        foreach ($phraseIds as $phraseName) {
            $text                     = $this->getPhraseText($phraseName, $language, true);
            $phraseTexts[$phraseName] = $text;
        }

        if ($starPatterns || $regexPatterns) {
            $this->_loadPendingPhraseGroups();
            foreach ($this->_phrases[$languageId] as $phraseName => $text) {
                foreach ($starPatterns as $pattern) {
                    if (Strings::isStarMatch($pattern, $phraseName)) {
                        $phraseTexts[$phraseName] = $text;
                    }
                }
                foreach ($regexPatterns as $pattern) {
                    if (preg_match($pattern, $phraseName)) {
                        $phraseTexts[$phraseName] = $text;
                    }
                }
            }
        }

        return $phraseTexts;
    }

    /**
     * Called when there is no such phrase name. By default this simply
     * returns null. But an event might change this.
     *
     * @param string   $phrase_name
     * @param Language $language
     *
     * @return string
     */
    protected function _noPhrase($phrase_name, $language)
    {
        $phrase = null;

        if (!isset(self::$_missing_phrases[$phrase_name])) {
            self::$_missing_phrases[$phrase_name] = $phrase_name;
        }

        if ($this->_event_dispatcher) {
            $evdata = new DataEvent(
                [
                    'phrase_name' => $phrase_name,
                    'language'    => $language,
                    'return'      => $phrase,
                ]
            );
            $this->_event_dispatcher->dispatch(self::EVENT_NO_PHRASE, $evdata);

            $phrase = $evdata->return;
        }

        return $phrase;
    }

    /**
     * Same as getPhraseText, except we run the phrase through the selector
     * to fetch the correct plural phrase for the given $count.
     *
     * @param string       $phrase_name The phrase you want to fetch
     * @param int          $count       The count
     * @param Language|int $language    The Language entity to use, or its id
     *
     * @return string
     */
    public function getPhraseTextCount($phrase_name, $count, $language = null)
    {
        if ($language === null) {
            $language = $this->_language;
        } elseif (Numbers::isInteger($language) && isset($this->_loaded_languages[$language])) {
            $language = $this->_loaded_languages[$language];
        }

        $cat = $language->selectPluralCategory($count);

        $try = [$phrase_name.'.'.$cat];
        if ($count === 0 && $cat !== 'zero') {
            // this tries 'zero' on langs that dont typically use it
            // i.e. this allows for a unique phrase for 0 in english like "You haven't created any departments yet."
            $try[] = $phrase_name.'.zero';
        }
        if ($cat !== 'other') {
            $try[] = $phrase_name.'.other';
        }
        $try[] = $phrase_name;

        foreach ($try as $tryPhraseId) {
            $t = $this->getPhraseText($tryPhraseId, $language, true);
            if ($t) {
                return $t;
            }
        }

        // try fallback on English
        if ($this->_default_language !== $language) {
            return $this->getPhraseTextCount($phrase_name, $count, $this->_default_language);
        }

        // otherwise missing phrase
        return $this->getPhraseText($try[0], $language);
    }

    /**
     * Get a phrase from a compatible object that knows how to describe a phrase ID.
     *
     * $property may be null, in which case it's expected to be a 'title' or 'name',
     * or the only item on the object that is translatable.
     *
     * @param mixed        $object           The object to get a phrase for
     * @param string       $property         A specific thing in the object to translate
     * @param Language|int $language         The Language entity to use, or its id
     * @param bool         $fallback_default
     *
     * @return string
     */
    public function getPhraseObject($object, $property = null, $language = null, $fallback_default = true)
    {
        //------------------------------
        // Standard translation interfaces
        //------------------------------

        if ($object instanceof DelegatePhraseInterface) {
            return $object->getPhrase($this, $language);
        } elseif ($object instanceof HasPhraseName) {
            $phraseNameRaw = $object->getPhraseName($property, $this);
            $phraseText    = false;
            $phraseName    = false;

            if (!is_array($phraseNameRaw)) {
                $phraseNameRaw = [$phraseNameRaw];
            }

            foreach ($phraseNameRaw as $usePhraseName) {
                if ($usePhraseName && $this->hasPhrase($usePhraseName, $language)) {
                    $phraseText = $this->phrase($usePhraseName, [], $language);
                    $phraseName = $usePhraseName;
                    break;
                }
            }

            if (!$phraseText) {
                if ($fallback_default) {
                    $phraseText = $object->getPhraseDefault($property, $this);
                } else {
                    return '';
                }
            }

            if ($phraseText) {
                return $phraseText;
            }

            return '';
        }

        //------------------------------
        // Phrase namer inspects objects..
        //------------------------------

        $namer      = $this->getObjectPhraseNamer();
        $phraseName = $namer->getPhraseName($object, $property);
        $phraseText = false;

        if ($phraseName && $this->hasPhrase($phraseName, $language)) {
            $phraseText = $this->phrase($phraseName, [], $language);
        }

        if (!$phraseText) {
            $phraseText = $this->getObjectPhraseNamer()->getPhraseDefault($object, $property);
        }

        if ($phraseText) {
            return $phraseText;
        }

        // No phrase
        return '';
    }

    /**
     * Fetch a phrase from the currently set language, and insert the passed variables into the placeholders.
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
     * @param string       $phrase_name The phrase to fetch
     * @param array        $vars        Variables to place into the phrase
     * @param Language|int $language    The Language entity to use, or its id
     *
     * @return string
     */
    public function phrase($phrase_name, array $vars = [], $language = null)
    {
        $lang = $language;
        if (!$lang) {
            $lang = $this->_language;
        }
        if ($lang && !($lang instanceof Language)) {
            $lang = @$this->_loaded_languages[$lang];
        }

        $debug = null;
        if ($lang && substr($lang->getSystemName(), 0, 4) === 'dev_') {
            $debug = $lang->getSystemName();
        }

        if (is_object($phrase_name) || (is_array($phrase_name) && is_object($phrase_name[0]))) {
            if (is_array($phrase_name)) {
                list($object, $property) = $phrase_name;
            } else {
                $object   = $phrase_name;
                $property = null;
            }

            $phraseText = $this->getPhraseObject($object, $property, $language);
        } elseif (isset($vars['count'])) {
            try {
                $phraseText = $this->getPhraseTextCount($phrase_name, $vars['count'], $language);
            } catch (\Exception $e) {
                // Fall back on just using a normal phrase without any pluralising
                // In case user modified phrase to remove the plural syntax
                $phraseText = $this->getPhraseText($phrase_name, $language);
            }
        } else {
            $phraseText = $this->getPhraseText($phrase_name, $language);
        }

        if (!$phraseText) {
            $phraseText = '';
        }

        $phraseText = $this->replaceVarsInString($phraseText, $vars);

        // A second pass detects phrase. replacements that might've been put in by replacements themselves
        $m = null;
        if (preg_match_all('#{{phrase\.([a-zA-Z0-9\-_\.]+)}}#', $phraseText, $m)) {
            foreach ($m[1] as $subPhraseName) {
                if ($subPhraseName == $phrase_name) {
                    continue;
                } //prevent loops
                $subPhraseText = $this->phrase($subPhraseName, $vars, $language);
                $phraseText    = str_replace("{{phrase.$subPhraseName}}", $subPhraseText, $phraseText);
            }
        }

        // Pass to detect which should be output as ng_Vars
        $m = null;
        if (preg_match_all('#ng_var\(([a-zA-Z0-9\-_\.]+)\)#', $phraseText, $m, \PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $phraseText = str_replace($match[0], '{{'.$match[1].'}}', $phraseText);
            }
        }

        if ($debug === 'dev_blankout') {
            $output = '';

            $len = min(4, strlen($phraseText));

            for ($i = 0; $i < $len; ++$i) {
                $output .= '█';
            }

            return $output;
        } elseif ($debug === 'dev_longstring') {
            // strings that are generally titles or button text etc, lets make them long to test overflow
            $len = strlen($phraseText);
            if ($len < 25) {
                $more = 25 - $len;
                $phraseText .= Strings::randomPronounceable($more, 3);
            }
        }

        return $phraseText;
    }

    /**
     * Translates the given message.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $translation = $this->phrase($id, $parameters, $this->localeToLanguage($locale));

        return $translation ?: $id;
    }

    /**
     * Translates the given choice message by choosing a translation according to a number.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param int         $number     The number to use to find the indice of the message
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $parameters['{count}'] = $number;

        $translation = $this->phrase($id, $parameters, $this->localeToLanguage($locale));

        return $translation ?: $id;
    }

    /**
     * @param null $locale
     *
     * @return LanguageEntity|null
     */
    private function localeToLanguage($locale = null)
    {
        static $locales = [];
        if (isset($locales[$locale])) {
            return $locales[$locale];
        }
        $chosenLanguage = null;
        if ($locale) {
            foreach ($this->_loaded_languages as $language) {
                $locales[$language->getLocale()] = $language;
                if ($language->getLocale() == $locale) {
                    $chosenLanguage = $language;
                    break;
                }
            }
        }

        return $chosenLanguage;
    }

    /**
     * Replaces {{vars}} form $vars in $phrase_text.
     *
     * @param       $phraseText
     * @param array $vars
     *
     * @return string
     */
    public function replaceVarsInString($phraseText, array $vars = [])
    {
        if ($vars) {
            $phraseText = preg_replace_callback(
                '#\{\{\s*([a-zA-Z0-9_]+)\s*\}\}#',
                function ($m) use ($vars) {
                    $name = $m[1];

                    if (isset($vars[$name])) {
                        return $vars[$name];
                    } elseif (isset($vars['_context'][$name])) {
                        return @htmlspecialchars($vars['_context'][$name], \ENT_QUOTES, 'UTF-8');
                    }

                    return '';
                },
                $phraseText
            );

            $phraseText = preg_replace_callback(
                '#\{\{\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*\}\}#',
                function ($m) use ($vars) {
                    $name = $m[1];
                    $prop = $m[2];

                    if (isset($vars[$name])) {
                        if (isset($vars[$name][$prop])) {
                            return $vars[$name][$prop];
                        } elseif (isset($vars[$name]->$prop)) {
                            return $vars[$name]->$prop;
                        }
                    } elseif (isset($vars['_context'][$name])) {
                        if (isset($vars['_context'][$name][$prop])) {
                            return @htmlspecialchars($vars['_context'][$name][$prop], \ENT_QUOTES, 'UTF-8');
                        } elseif (isset($vars['_context'][$name]->$prop)) {
                            return @htmlspecialchars($vars['_context'][$name]->$prop, \ENT_QUOTES, 'UTF-8');
                        }
                    } elseif ($prop) {
                        // If the top var exists, then its just an unset var
                        // so return empty string
                        if (isset($vars[$name]) || isset($vars['_context'][$name])) {
                            return '';
                        }
                    }

                    return $m[0];
                },
                $phraseText
            );
        }

        return $phraseText;
    }

    /**
     * Just like date() except D, l, F and M are replaced by translated strings.
     *
     * @param string        $format   A date format string
     * @param int|\DateTime $dateOrTs A DateTime object or a timestamp
     * @param string        $prefix
     *
     * @return string
     */
    public function date($format, $dateOrTs = null, $prefix = 'user.time.')
    {
        if (!$dateOrTs) {
            $dateOrTs = time();
        }

        $ts = $dateOrTs;
        if ($ts instanceof \DateTime) {
            $tzOffset = $ts->format('P');

            // getTimestamp will return the underlaying timestamp of the Date,
            // it doesnt apply any timezone offsets. So we'll need to convert it now
            $ts = \Orb\Util\Dates::makeUtcDateTime($dateOrTs);

            if (!$ts) {
                return 'invalid_date';
            }

            $ts = $ts->getTimestamp();
        } else {
            $tzOffset = '+00:00';
        }

        // D: Mon
        // l: Monday
        // F: January
        // M: Jan

        $format = preg_replace('#(?<!\\\\)([DlFMP])#', '\\\\D\\\\P-\\\\$1', $format);
        $date   = date($format, $ts);

        $tr   = $this;
        $date = preg_replace_callback(
            '#DP\-([DlFMP])#',
            function ($m) use ($prefix, $tr, $ts, $tzOffset) {
                switch ($m[1]) {
                    case 'D':
                        $phraseName = $prefix.'short-day_'.strtolower(date('l', $ts));
                        break;
                    case 'l':
                        $phraseName = $prefix.'long-day_'.strtolower(date('l', $ts));
                        break;
                    case 'F':
                        $phraseName = $prefix.'long-month_'.strtolower(date('F', $ts));
                        break;
                    case 'M':
                        $phraseName = $prefix.'short-month_'.strtolower(date('F', $ts));
                        break;
                    case 'P':
                        return $tzOffset;
                    default:
                        // never matches
                        return 'unkown segment';
                }

                return $tr->getPhraseText($phraseName);
            },
            $date
        );

        return $date;
    }

    /**
     * Translate result from \Orb\Util\Dates::secsToReadable
     * Result example: `27 days 23 hours 21 minutes 54 seconds`.
     *
     * @param int    $seconds The seconds
     * @param int    $detail  How much detail to go into, 1-5
     * @param string $prefix
     *
     * @return string
     */
    public function secsToReadable($seconds, $detail = 2, $prefix = 'user.time.')
    {
        // example: `27 days 23 hours 21 minutes 54 seconds`
        $readable = \Orb\Util\Dates::secsToReadable($seconds, $detail);

        $tr = $this;

        return preg_replace_callback(
            '#[0-9]+ [^0-9 ]+#',
            function ($m) use ($prefix, $tr) {
                // expect $m[0] to be something like `27 days`
                $parts = explode(' ', $m[0]);
                if (count($parts) !== 2) {
                    return $m[0];
                }

                switch ($parts[1]) {
                    case 'years':
                        $phraseName = $prefix.'x_year';
                        break;
                    case 'days':
                        $phraseName = $prefix.'x_day';
                        break;
                    case 'hours':
                        $phraseName = $prefix.'x_hour';
                        break;
                    case 'minutes':
                        $phraseName = $prefix.'x_minute';
                        break;
                    case 'seconds':
                        $phraseName = $prefix.'x_second';
                        break;
                    default:
                        // never matches
                        return 'unkown segment';
                }

                return $tr->phrase($phraseName, ['count' => (int) $parts[0]]);
            },
            $readable
        );
    }

    /**
     * Check to see if a phrase exists.
     *
     * @param string $phrase_name
     *
     * @return bool
     */
    public function hasPhrase($phrase_name, $language = null)
    {
        if ($this->getPhraseText($phrase_name, $language, true) !== null) {
            return true;
        }

        return false;
    }

    /**
     * @return ObjectPhraseNamer
     */
    public function getObjectPhraseNamer()
    {
        if ($this->_phrase_object_namer !== null) {
            return $this->_phrase_object_namer;
        }

        $this->_phrase_object_namer = new ObjectPhraseNamer();

        return $this->_phrase_object_namer;
    }

    /**
     * Given an Entity this returns the phrase for the first lang in $lang_priority.
     *
     * $lang_priority is an array of languages or lang ID's.
     * Or you can pass multiple values (variable number of args) and all
     * trailing args will be considered lang Ids.
     *
     * @param mixed  $object
     * @param string $property
     * @param array  $langPriority
     *
     * @return string
     */
    public function objectChoosePhraseText($object, $property, $langPriority)
    {
        $args = func_get_args();
        array_shift($args);
        array_shift($args);

        //------------------------------
        // Build priority array
        //------------------------------

        // Verifies lang params, converts lang IDs to objects

        $langPriority = [];
        foreach ($args as $arg) {
            if (!is_array($arg)) {
                $arg = [$arg];
            }

            foreach ($arg as $l) {
                if (!$l) {
                    continue;
                }

                if (is_numeric($l)) {
                    $l = App::getContainer()->getLanguageData()->get($l);
                }

                if ($l instanceof Language) {
                    $langPriority[] = $l;
                }
            }
        }

        //------------------------------
        // Pick the lang text
        //------------------------------

        $objLangRepos = App::getContainer()->getObjectLangRepository();

        foreach ($langPriority as $lang) {
            $objLangRepos->preloadObject($lang, $object);
        }

        $rec = $objLangRepos->getRec($langPriority, $object, $property, true);

        if (!$rec) {
            return '';
        }

        return $rec->value;
    }
}
