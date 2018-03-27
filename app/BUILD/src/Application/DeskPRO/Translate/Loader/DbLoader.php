<?php

/**
 * DeskPRO.
 *
 * @category Translate
 */

namespace Application\DeskPRO\Translate\Loader;

use Application\DeskPRO\App;

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
     * Plain database connection for raw queries.
     *
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
        $phrases = [];

        // Langs to fetch in order of pri
        $langs = [];
        if ($language) {
            if (!$language->getId()) {
                //todo default lang should be injected somehow,
                //but theres a problem of cyclic depends so this is an ok solution for now
                $language = App::$container->getLanguageData()->getDefault();
            }
            if ($language && $language->getId()) {
                $langs[] = $language->getId(); // the chosen lang
            }
        }
        $langs[] = $this->default_lang_id; // default deskpro lang

        foreach ($langs as $lid) {
            foreach ($groups as $g) {
                if (empty($this->loaded[$lid][$g])) {
                    continue;
                }

                // obj_ translations only apply for specific language
                // being reuqested (e.g., no english fallthrough)
                if ($language && $lid != $language->id && substr($g, 0, 4) === 'obj_') {
                    continue;
                }

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
                        if (empty($phrases[$phr_id])) {
                            $phrases[$phr_id] = $phr;
                        }
                    }
                }
            }
        }

        return $phrases;
    }

    /**
     * {@inheritdoc}
     */
    public function load($groups, $language, array $loaded_phrases = null)
    {
        if ($this->loaded !== null) {
            return $this->returnPhrases($groups, $language, $loaded_phrases);
        }

        $q = $this->dbconn->query("
            SELECT language_id, groupname, name, COALESCE(NULLIF(phrase, ''), original_phrase) AS phrase
            FROM phrases
        ");

        $this->loaded = [];
        while ($r = $q->fetch()) {
            if (!isset($this->loaded[$r['language_id']])) {
                $this->loaded[$r['language_id']] = [];
            }
            if (!isset($this->loaded[$r['language_id']][$r['groupname']])) {
                $this->loaded[$r['language_id']][$r['groupname']] = [];
            }

            $this->loaded[$r['language_id']][$r['groupname']][$r['name']] = $r['phrase'];
        }

        return $this->returnPhrases($groups, $language, $loaded_phrases);
    }
}
