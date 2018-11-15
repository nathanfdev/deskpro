<?php

namespace Application\DeskPRO\Languages;

use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LangPackInfo.
 */
class LangPackInfo
{
    /**
     * @var string
     */
    private $langDir;

    /**
     * @var array
     */
    protected $manifest;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->langDir = DP_ROOT.DIRECTORY_SEPARATOR.'/locales';

        // in prod, manifest is compiled to fs
        if (is_file($this->langDir.DIRECTORY_SEPARATOR.'manifest.php')) {
            $this->manifest = require $this->langDir.DIRECTORY_SEPARATOR.'manifest.php';

        // otherwise read from each dir
        } else {
            $locales = ListUtils::filterMap(
                Finder::create()->directories()->in($this->langDir)->exclude(1),
                function (\SplFileInfo $d) {
                    if (
                        file_exists($d->getPathname().DIRECTORY_SEPARATOR.'localeInfo.yml')
                        || file_exists($d->getPathname().DIRECTORY_SEPARATOR.'localeInfo.php')
                    ) {
                        return $d->getFilename();
                    } else {
                        return null;
                    }
                }
            );

            $this->manifest = MapUtils::map($locales, function ($idx, $locale) {
                $data = $this->readLocaleDataFile($locale, 'localeInfo');

                return [$data['id'], $data];
            });
        }

        // backwards compat
        $this->manifest = MapUtils::mapValues($this->manifest, function ($id, $l) {
            $l['title'] = $l['name'];
            $l['lang_code'] = $l['locale'];

            return $l;
        });
    }

    /**
     * @param string $locale
     * @param string $name
     *
     * @return string
     */
    private function readLocaleDataFile($locale, $name)
    {
        $base = $this->langDir.DIRECTORY_SEPARATOR.$locale.DIRECTORY_SEPARATOR.$name;

        if (is_file("$base.php")) {
            return require "$base.php";
        } elseif (is_file("$base.yml")) {
            return Yaml::parse(file_get_contents("$base.yml"));
        } else {
            throw new \RuntimeException("Cant load file: $locale/$name");
        }
    }

    /**
     * @return string
     */
    public function getLangDir()
    {
        return $this->langDir;
    }

    /**
     * @return array
     */
    public function getLangIds()
    {
        return array_keys($this->manifest);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasLang($id)
    {
        return isset($this->manifest[$id]);
    }

    /**
     * @return array
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     * Return a list of all lang packs.
     *
     * @return array
     */
    public function getLangPacks()
    {
        return array_values($this->manifest);
    }

    /**
     * Fetches info about a language.
     *
     * $key can be:
     * - null: Array of all info
     * - id: The lang id
     * - title: Readable English title of the language
     * - titleLocal: The language name in the local language
     * - locale: The locale
     *
     * @param string      $id
     * @param string|null $key
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function getLangInfo($id, $key = null)
    {
        if (!isset($this->manifest[$id])) {
            throw new \InvalidArgumentException("Unknown language $id");
        }

        $info = $this->manifest[$id];

        if ($key) {
            if (!isset($info[$key])) {
                return;
            }

            return $info[$key];
        }

        return $info;
    }

    /**
     * Get lang titles as id=>title.
     *
     * @return array
     */
    public function getLangTitles($local = false)
    {
        return MapUtils::mapValues($this->manifest, function ($id, $l) use ($local) {
            return $local ? $l['nameLocal'] : $l['name'];
        });
    }

    /**
     * @param string $id
     *
     * @return \Application\DeskPRO\Entity\Language
     */
    public function newLanguageEntity($id)
    {
        if (!$this->hasLang($id)) {
            throw new \InvalidArgumentException();
        }

        $lang                = new \Application\DeskPRO\Entity\Language();
        $lang->sys_name      = $this->getLangInfo($id, 'id');
        $lang->title         = $this->getLangInfo($id, 'nameLocal');
        $lang->locale        = $this->getLangInfo($id, 'locale');
        $lang->is_rtl        = $this->getLangInfo($id, 'isRtl');
        $lang->has_user      = true;
        $lang->has_agent     = true;
        $lang->has_admin     = true;
        $lang->base_filepath = '%DP_ROOT%/locales/'.$lang->getLocale();

        return $lang;
    }
}
