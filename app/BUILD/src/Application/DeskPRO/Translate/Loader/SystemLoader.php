<?php

/**
 * DeskPRO.
 *
 * @category Translate
 */

namespace Application\DeskPRO\Translate\Loader;

use DeskPRO\Component\Util\MapUtils;
use DpSys\CodePlugin\DpPlugins;
use Orb\Util\Arrays;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads default phrases from filesystem-based lang packs.
 */
class SystemLoader implements LoaderInterface
{
    private static $groupFileMap = [
        'adm'     => 'backend',
        'admin'   => 'backend',
        'reports' => 'backend',
        'api'     => 'backend',
        'agent'   => 'backend',
        'general' => 'backend',
        'portal'  => 'user',
        'user'    => 'user',
    ];

    /**
     * Array of filepath => array.
     *
     * @var array
     */
    private $loadedResources = [];

    /**
     * {@inheritdoc}
     */
    public function load($groups, $language, array $loaded_phrases = null)
    {
        $lang_packs = [];

        // Always read from the default because it has the core phrases
        $lang_packs[] = DP_ROOT.'/locales/en-US';

        if ($language && $language->base_filepath) {
            $lang_packs[] = str_replace('%DP_ROOT%', DP_ROOT, $language->base_filepath);
        }

        $lang_packs = array_unique($lang_packs);
        $lang_packs = Arrays::removeFalsey($lang_packs);

        $phrases = [];

        foreach ($lang_packs as $path) {
            foreach ($groups as $group) {
                $groupParts = explode('.', $group, 2);

                // prevent incorrect phrase names
                if (count($groupParts) < 2) {
                    continue;
                }

                if (isset(self::$groupFileMap[$groupParts[0]])) {
                    $filePhrases = $this->loadFile($path, self::$groupFileMap[$groupParts[0]]);
                    if ($filePhrases && isset($filePhrases[$groupParts[0]][$groupParts[1]])) {
                        $phrases = array_merge($phrases, $filePhrases[$groupParts[0]][$groupParts[1]]);
                    }
                }
            }
        }

        return $phrases;
    }

    /**
     * @param string $file
     *
     * @return array
     */
    private function loadFile($localeDir, $name)
    {
        $base = $localeDir.DIRECTORY_SEPARATOR.$name;
        if (isset($this->loadedResources[$base])) {
            return $this->loadedResources[$base];
        }

        $relFile = basename($localeDir)."/$name.php";

        if (is_file("$base.php")) {
            $filePhrases = require "$base.php";
        } elseif (is_file("$base.yml")) {
            $filePhrases = Yaml::parse(file_get_contents("$base.yml"));
        } else {
            $filePhrases = null;
        }

        if ($filePhrases !== null) {
            $filePhrases = array_merge(
                $filePhrases,
                DpPlugins::getManager()->loadExtraLangFile($relFile)
            );

            $filePhrases = MapUtils::flattenKeys($filePhrases);

            foreach ($filePhrases as $phraseName => $phraseTranslation) {
                $groupParts = explode('.', $phraseName, 3);

                $this->loadedResources[$base][$groupParts[0]][$groupParts[1]][$phraseName] = $phraseTranslation;
            }
        }

        if (!isset($this->loadedResources[$base])) {
            $this->loadedResources[$base] = [];
        }

        return $this->loadedResources[$base];
    }
}
