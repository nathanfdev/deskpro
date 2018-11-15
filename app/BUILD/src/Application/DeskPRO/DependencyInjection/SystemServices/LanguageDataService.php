<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Language;
use Orb\Util\Arrays;

class LanguageDataService extends BaseRepositoryService
{
    /**
     * @var bool
     */
    protected $has_init = false;

    /**
     * @var int
     */
    protected $default_lang_id = 1;

    /**
     * Loaded langs.
     *
     * @var array
     */
    protected $languages = [];

    /**
     * @var int
     */
    protected $count = 1;

    public static function create(DeskproContainer $container, array $options = null)
    {
        if (!$options) {
            $options = [];
        }
        $options['entity']          = 'Application\\DeskPRO\\Entity\\Language';
        $options['default_lang_id'] = $container->getSetting('core.default_language_id');

        $em = $container->getEm();
        $o  = new static($em, $options);

        return $o;
    }

    public function init()
    {
        $this->default_lang_id = (int) $this->options->get('default_lang_id');
    }

    /**
     * @return bool
     */
    public function isLangSystemEnabled()
    {
        return $this->isMultiLang();
    }

    /**
     * True to enable multi-language interfaces. Languages might be enabled, but if only one
     * lang exists then it effectively means that the interface should still act as though
     * its disabled.
     *
     * @return bool
     */
    public function isMultiLang()
    {
        $this->preload();

        return $this->count > 1;
    }

    /**
     * Find a language by a lang code.
     *
     * @param string $code
     *
     * @return \Application\DeskPRO\Entity\Language|null
     */
    public function findLangCode($code)
    {
        $this->preload();

        /** @var Language $lang */
        foreach ($this->languages as $lang) {
            if ($lang->getLocale() === $code || substr($lang->getLocale(), 0, 2) == $code) {
                return $lang;
            }
        }

        return;
    }

    /**
     * Get an array of locale codes.
     *
     * @return string[]
     */
    public function getLocaleCodes()
    {
        $this->preload();
        $codes = [];

        /** @var Language $lang */
        foreach ($this->languages as $lang) {
            $codes[] = substr($lang->getLocale(), 0, 2);
        }

        return $codes;
    }

    /**
     * @return \Application\DeskPRO\Entity\Language
     */
    public function getDefault()
    {
        return $this->default_lang_id ? $this->em->getRepository(Language::class)->find($this->default_lang_id) : null;
    }

    /**
     * @return int
     */
    public function getDefaultId()
    {
        return $this->default_lang_id;
    }

    /**
     * @param $id
     *
     * @return \Application\DeskPRO\Entity\Language
     */
    public function get($id)
    {
        $this->preload();

        return isset($this->languages[$id]) ? $this->languages[$id] : null;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->preload();

        return $this->count;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function has($id)
    {
        $this->preload();

        return isset($this->languages[$id]);
    }

    /**
     * Loads the required data.
     *
     * @return mixed
     */
    protected function preload()
    {
        if ($this->has_init) {
            return;
        }
        $this->has_init = true;

        $this->languages = $this->em->createQuery('
            SELECT l
            FROM DeskPRO:Language l INDEX BY l.id
            ORDER BY l.title ASC
        ')->execute();

        $this->count = count($this->languages);
    }

    /**
     * Get languages by ID.
     *
     * @param array $ids
     * @param bool  $keep_order
     *
     * @return \Application\DeskPRO\Entity\Language[]
     */
    public function getByIds(array $ids, $keep_order = false)
    {
        $this->preload();
        $ret = [];

        foreach ($ids as $id) {
            if (isset($this->languages[$id])) {
                $ret[$id] = $this->languages[$id];
            }
        }

        if ($keep_order) {
            Arrays::orderIdArray($ids, $ret);
        }

        return $ret;
    }

    /**
     * @return \Application\DeskPRO\Entity\Language[]
     */
    public function getAll()
    {
        $this->preload();

        return $this->languages;
    }

    /**
     * Get names of langs.
     *
     * @param array|null $for_ids
     *
     * @return string[]
     */
    public function getTitles(array $for_ids = null)
    {
        $this->preload();
        $ret = [];

        if (!$for_ids) {
            $for_ids = array_keys($this->languages);
        }

        foreach ($for_ids as $id) {
            $ret[$id] = $this->languages[$id]->getTitle();
        }

        return $ret;
    }
}
