<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Domain;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\ObjectLang;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class ObjectTranslatable.
 *
 * @deprecated Use ObjectTranslatableInterface instead
 */
class ObjectTranslatable
{
    /**
     * @var DomainObject
     */
    protected $entity;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $unsaved = [];

    /**
     * @var \Application\DeskPRO\Entity\Language
     */
    protected $lang;

    /**
     * @var null
     */
    protected $with_lang_prop = null;

    /**
     * @var null
     */
    protected $try_langs = null;

    /**
     * Config:.
     *
     * - with_lang_prop: When true, we consider the object itself defines default translation
     * data. For example, Article has $title and with_lang_prop as 'language'. Getting the 'title' property in $language therefore just results in
     * $title being returned, rather than using the ObjectLangRepository.
     * When getting a primary string like this, the getRealX method is called upon (e.g., 'getRealTitle' in this example).
     *
     * @param DomainObject $entity
     * @param array        $config
     */
    public function __construct(DomainObject $entity, $config)
    {
        $this->entity = $entity;
        $this->config = $config;

        if (!empty($config['with_lang_prop'])) {
            $this->with_lang_prop = $config['with_lang_prop'];
        }

        // Warning: This object should not do any ORM loading in construct
        // because it is called during postLoad which causes problems in Doctrine
    }

    /**
     * @param Language $lang
     */
    public function setLang(Language $lang = null)
    {
        $this->lang = $lang;
    }

    /**
     * Set the default languages to try (in order). These are used when $lang is null in the get prop methods.
     */
    public function setTryLangs(array $try_langs = null)
    {
        $this->try_langs = $try_langs;
    }

    /**
     * Gets the try langs.
     *
     * If no try langs have been set explicity with setTryLangs(), we will try langs based on the try langs
     * set by the ObjectLangRepository. If this object has a with_lang_prop, that will always be tried
     * last if it doesnt appear in the array.
     *
     * @return array
     */
    public function getTryLangs()
    {
        if ($this->try_langs) {
            return $this->try_langs;
        }

        $try = $this->getObjLangRepos()->getTryLangs();

        if ($this->with_lang_prop) {
            $try[] = $this->entity[$this->with_lang_prop];
        }
        if ($this->lang) {
            $try[] = $this->lang;
        }

        return $try;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return App::getOrm();
    }

    /**
     * @return \Application\DeskPRO\Translate\ObjectLangRepository
     */
    public function getObjLangRepos()
    {
        return App::getSystemService('object_lang_repository');
    }

    /**
     * @param string   $prop
     * @param Language $lang
     *
     * @return string
     */
    public function getObjectProp($prop, $lang = null)
    {
        if (!$lang) {
            $lang = $this->getTryLangs();
        }

        $langs = is_array($lang) ? $lang : [$lang];

        foreach ($langs as $lang) {
            if (!$lang) {
                continue;
            }
            if (!is_object($lang)) {
                $lang = App::getContainer()->getLanguageData()->get($lang);
                if (!$lang) {
                    throw new \InvalidArgumentException();
                }
            }

            if ($this->with_lang_prop && $this->entity[$this->with_lang_prop] && $this->entity[$this->with_lang_prop]->getId() == $lang->getId()) {
                $method = "getReal$prop";

                return $this->entity->$method();
            }

            if (!$this->entity->getId()) {
                $prop    = strtolower($prop);
                $lang_id = $lang->getId();

                if (isset($this->unsaved[$lang_id][$prop])) {
                    return $this->unsaved[$lang_id][$prop]->text;
                }
            } else {
                $ret = $this->getObjLangRepos()->get($lang, $this->entity, $prop);
                if ($ret) {
                    return $ret;
                }
            }
        }

        return '';
    }

    /**
     * @param string $prop
     * @param string $value
     */
    public function setObjectProp($prop, $value, $lang = null)
    {
        if ($lang === null) {
            if ($this->with_lang_prop) {
                $lang = $this->entity[$this->with_lang_prop];
            }
            if ($this->lang) {
                $lang = $this->lang;
            }
        }
        if (!is_object($lang)) {
            $lang = App::getContainer()->getLanguageData()->get($lang);
            if (!$lang) {
                throw new \InvalidArgumentException();
            }
        }

        if ($this->with_lang_prop && $this->entity[$this->with_lang_prop]->getId() == $lang->getId()) {
            $method = "setReal$prop";
            $this->entity->$method($value);

            return;
        }

        if (!$this->entity->getId()) {
            $prop    = strtolower($prop);
            $lang_id = $lang->getId();

            $rec = isset($this->unsaved[$lang_id][$prop]) ? $this->unsaved[$lang_id][$prop] : null;
            if (!$rec) {
                $rec = ObjectLang::createObjectLang($lang, $this->entity, $prop, $value);
            }
            $rec->text = $value;

            if (!isset($this->unsaved[$lang_id])) {
                $this->unsaved[$lang_id] = [];
            }
            $this->unsaved[$lang_id][$prop] = $rec;

            return $rec;
        }

        $this->getObjLangRepos()->setRec($lang, $this->entity, $prop, $value);

        return;
    }

    //###################################################################################################################

    public function _dynGetObjectProp($flags, $call_args)
    {
        $ret = $this->getObjectProp($flags['property']);
        if ($ret) {
            $mod = $flags['property'].'Modifier';
            if (method_exists($this->entity, $mod)) {
                $ret = $this->entity->$mod($ret);
            }
        }

        return $ret;
    }

    public function _dynSetObjectProp($flags, $call_args)
    {
        return $this->setObjectProp($flags['property'], $call_args[0]);
    }

    public function _dynSetLanguage($flags, $call_args)
    {
        $lang = $call_args[0];
        if (!is_object($lang)) {
            $lang = App::getContainer()->getDataService('Language')->getDefault();
        }

        if (!$lang || !($lang instanceof Language)) {
            throw new \InvalidArgumentException('Invalid language');
        }

        $this->lang = $lang;
    }

    public function _dpTranslatePersistChanges()
    {
        if ($this->unsaved) {
            foreach ($this->unsaved as $group) {
                /** @var ObjectLang $rec */
                foreach ($group as $rec) {
                    $rec->setObject($this->entity);

                    $this->getEm()->persist($rec);
                    $this->getEm()->flush();
                }
            }

            $this->unsaved = [];
        }

        if ($this->entity->getId()) {
            foreach ($this->getObjLangRepos()->getLoadedRecs($this->entity) as $prop_recs) {
                foreach ($prop_recs as $rec) {
                    $this->getEm()->persist($rec);
                    $this->getEm()->flush();
                }
            }
        }
    }

    //###################################################################################################################

    /**
     * @param DomainObject $object
     *
     * @return ObjectTranslatable
     */
    public static function loadObjectTranslatable(DomainObject $object)
    {
        if (isset($object->_dp_object_translatable) && $object->_dp_object_translatable) {
            return $object->_dp_object_translatable;
        }

        $config                          = $object::loadObjectTranslatableMetadata();
        $object->_dp_object_translatable = new self($object, $config);

        foreach ($config['fields'] as $f) {
            $fl = strtolower($f);
            $object->addCustomCallable("get$fl", [$object->_dp_object_translatable, '_dynGetObjectProp'], ['property' => $f]);
            $object->addCustomCallable("set$fl", [$object->_dp_object_translatable, '_dynSetObjectProp'], ['property' => $f]);
        }

        $object->addCustomCallable('setTranslateLanguage', [$object->_dp_object_translatable, '_dynSetLanguage']);

        return $object->_dp_object_translatable;
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadEntityMetadata(ClassMetadata $metadata)
    {
        $metadata->addLifecycleCallback('getObjectTranslatable', 'postLoad');
        $metadata->addLifecycleCallback('persistTranslatable', 'postPersist');
    }
}
