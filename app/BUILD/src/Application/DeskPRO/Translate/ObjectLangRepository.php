<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Translate;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ObjectLang;
use Doctrine\ORM\EntityManager;

/**
 * Class ObjectLangRepository.
 *
 * @deprecated use ObjectTranslatableInterface instead
 */
class ObjectLangRepository
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * Loaded lang objects.
     *
     * @var array
     */
    protected $loaded = [];

    /**
     * @var array
     */
    protected $queued_objects;

    /**
     * @var array
     */
    protected $try_langs = [];

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Set the default languages to try (in order). These are used when $lang is null in the get prop methods.
     *
     * @param \Application\DeskPRO\Entity\Language[] $try_langs
     */
    public function setTryLangs(array $try_langs)
    {
        $this->try_langs = $try_langs;
    }

    /**
     * @return \Application\DeskPRO\Entity\Language[]
     */
    public function getTryLangs()
    {
        if (!$this->try_langs) {
            $try_langs = [];
            if (App::getCurrentPerson()) {
                $l = App::getCurrentPerson()->getRealLanguage();
                if ($l) {
                    $try_langs[$l->getId()] = $l;
                }
            }
            if ($l = App::getTranslator()->getLanguage()) {
                $try_langs[$l->getId()] = $l;
            }

            $l = App::getContainer()->getLanguageData()->getDefault();
            if ($l) {
                $try_langs[$l->getId()] = $l;
            }

            return $try_langs;
        }

        return $this->try_langs;
    }

    /**
     * @param int|\Application\DeskPRO\Entity\Language $lang
     * @param object|string                            $object
     *
     * @return bool
     */
    public function isLoaded($lang, $object)
    {
        $lang_id = is_object($lang) ? $lang->getId() : $lang;
        $obj_ref = is_object($object) ? $object->getObjectRef() : $object;

        return isset($this->loaded[$obj_ref][$lang_id]);
    }

    /**
     * Get all objects loaded on a rec.
     *
     * @param object|string $object
     *
     * @return \Application\DeskPRO\Entity\ObjectLang[]
     */
    public function getLoadedRecs($object)
    {
        $obj_ref = is_object($object) ? $object->getObjectRef() : $object;

        if (!isset($this->loaded[$obj_ref])) {
            return [];
        }

        $recs = [];
        foreach ($this->loaded[$obj_ref] as $lang_id => $lang_recs) {
            foreach ($lang_recs as $rec) {
                $prop = $rec->prop_name;
                if (!isset($recs[$prop])) {
                    $recs[$prop] = [];
                }
                $recs[$prop][$lang_id] = $rec;
            }
        }

        return $recs;
    }

    /**
     * Get the ObjectLang record for a given property. Returns null if no such record exists.
     *
     * @param int|\Application\DeskPRO\Entity\Language $lang      A language or array of languages. If an array, the first existing will be returned
     * @param object|string                            $object    The object to get the property on
     * @param string                                   $prop_name The property to get
     * @param string                                   $fallback  True to the 'try langs' if $lang is not found
     *
     * @return \Application\DeskPRO\Entity\ObjectLang
     */
    public function getRec($lang, $object, $prop_name, $fallback = false)
    {
        $prop_name = strtolower($prop_name);

        $try_langs = is_array($lang) ? $lang : [$lang];
        $done      = [];
        if ($fallback) {
            $try_langs = array_merge($try_langs, $this->getTryLangs());
        }

        foreach ($try_langs as $lang) {
            $lang_id = is_object($lang) ? $lang->getId() : $lang;

            if (isset($done[$lang_id])) {
                continue;
            }
            $done[$lang_id] = true;

            $obj_ref = is_object($object) ? $object->getObjectRef() : $object;

            if (!$this->isLoaded($lang_id, $obj_ref)) {
                $this->preloadObject($lang_id, $obj_ref);
                $this->runPreload();
            }

            if (isset($this->loaded[$obj_ref][$lang_id][$prop_name])) {
                return $this->loaded[$obj_ref][$lang_id][$prop_name];
            }
        }

        return;
    }

    /**
     * Sets the value on a phrase lang. A new record will be created automatically if one doesnt exist.
     *
     * @param int|\Application\DeskPRO\Entity\Language $lang
     * @param object|string                            $object
     * @param string                                   $prop_name
     * @param string                                   $text
     *
     * @return ObjectLang
     */
    public function setRec($lang, $object, $prop_name, $text)
    {
        $rec = $this->getRec($lang, $object, $prop_name);

        if (!$rec) {
            // Need to do manual lookup because getRec wont return
            // empty values, so the record itself might still exist

            $lang_id = is_object($lang) ? $lang->getId() : $lang;
            $obj_ref = is_object($object) ? $object->getObjectRef() : $object;

            $rec = $this->em->createQuery('
                SELECT o
                FROM DeskPRO:ObjectLang o
                WHERE o.ref = ?0 AND o.prop_name = ?1 AND o.language = ?2
            ')->setParameters([$obj_ref, $prop_name, $lang_id])->getOneOrNullResult();
        }

        if (!$rec) {
            $rec = ObjectLang::createObjectLang($lang, $object, $prop_name, $text);
            $this->registerRec($rec);
        }

        $rec->setValue($text);

        return $rec;
    }

    /**
     * Get the value of a given property. This is the actual translated text.
     *
     * @param int|\Application\DeskPRO\Entity\Language $lang      A language or array of languages. If an array, the first existing will be returned
     * @param object|string                            $object    The object to get the property on
     * @param string                                   $prop_name The property to get
     * @param string                                   $fallback  True to the 'try langs' if $lang is not found
     *
     * @return string
     */
    public function get($lang, $object, $prop_name, $fallback = false)
    {
        $rec = $this->getRec($lang, $object, $prop_name, $fallback);
        if (!$rec) {
            return;
        }

        return $rec->getValue();
    }

    /**
     * Registeres an object lang record onto this object. E.g., it might be one that we are about
     * to persist, or one we want to keep unpersisted.
     *
     * @param \Application\DeskPRO\Entity\ObjectLang $rec
     */
    public function registerRec($rec)
    {
        $lang_id = $rec->language->getId();
        $obj_ref = $rec->ref;

        // If we add this one new record, the repository
        // will think we have preloaded it. So we should make
        // sure we have all of the objects loaded already
        if (!$this->isLoaded($lang_id, $obj_ref)) {
            $this->preloadObject($lang_id, $obj_ref);
            $this->runPreload();
        }

        if (!isset($this->loaded[$obj_ref])) {
            $this->loaded[$obj_ref] = [];
        }
        if (!isset($this->loaded[$obj_ref][$lang_id])) {
            $this->loaded[$obj_ref][$lang_id] = [];
        }

        $this->loaded[$obj_ref][$lang_id][$rec->prop_name] = $rec;
    }

    /**
     * Mark an object for preloading.
     *
     * @param int|\Application\DeskPRO\Entity\Language $lang   Lang, array of langs or null for getTryLangs
     * @param object                                   $object
     */
    public function preloadObject($lang, $object)
    {
        if ($lang === null) {
            $lang = $this->getTryLangs();
        }

        $langs = is_array($lang) ? $lang : [$lang];

        // Automatically queue up try langs as well
        $langs = array_merge($langs, $this->getTryLangs());
        $done  = [];

        foreach ($langs as $lang) {
            $lang_id = is_object($lang) ? $lang->getId() : $lang;

            if (isset($done[$lang_id])) {
                continue;
            }
            $done[$lang_id] = true;

            $obj_ref = is_object($object) ? $object->getObjectRef() : $object;

            if (isset($this->loaded[$obj_ref][$lang_id])) {
                return;
            }

            if (!isset($this->queued_objects[$lang_id])) {
                $this->queued_objects[$lang_id] = [];
            }

            $this->queued_objects[$lang_id][$obj_ref] = $obj_ref;
        }
    }

    /**
     * @param int|\Application\DeskPRO\Entity\Language $lang       Lang, array of langs or null for getTryLangs
     * @param array                                    $collection
     */
    public function preloadObjectCollection($lang, $collection)
    {
        foreach ($collection as $obj) {
            $this->preloadObject($lang, $obj);
        }
    }

    /**
     * Exec the query that fetches any langs we have queued up.
     */
    public function runPreload()
    {
        if (!$this->queued_objects) {
            return;
        }

        $run                  = $this->queued_objects;
        $this->queued_objects = [];

        $run_refs  = [];
        $run_langs = [];

        foreach ($run as $lang_id => $refs) {
            foreach ($refs as $ref => $v) {
                $run_refs[$ref]      = 1;
                $run_langs[$lang_id] = 1;

                if (!isset($this->loaded[$ref][$lang_id])) {
                    $this->loaded[$ref][$lang_id] = [];
                }
            }
        }

        $run_refs  = array_keys($run_refs);
        $run_langs = array_keys($run_langs);

        // Possible we over-fetch some info by getting
        // langs we didnt specify if we are pre-loading two sets at a time
        // but better to over-fetch than under-fetch and do another query
        $recs = $this->em->createQuery('
            SELECT o
            FROM DeskPRO:ObjectLang o
            WHERE o.ref IN (?0) AND o.language IN (?1)
        ')->setParameters([$run_refs, $run_langs])->execute();

        foreach ($recs as $rec) {
            if (!trim($rec->value)) {
                continue;
            }

            $obj_ref = $rec->ref;
            $lang_id = $rec->language->getId();

            if (!isset($this->loaded[$obj_ref][$lang_id])) {
                $this->loaded[$obj_ref][$lang_id] = [];
            }

            $this->loaded[$obj_ref][$lang_id][$rec->getPropName()] = $rec;
        }
    }

    /**
     * Clear the loaded lang objects.
     *
     * @param null $object Optionally only clear this one object
     */
    public function clear($object = null)
    {
        if ($object) {
            $ref = $object->getObjectRef();
            unset($this->loaded[$ref]);
        } else {
            $this->loaded = [];
        }
    }
}
