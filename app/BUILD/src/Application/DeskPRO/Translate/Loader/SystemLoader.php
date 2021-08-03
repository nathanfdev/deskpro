<?php

namespace Application\DeskPRO\Translate\Loader;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use DeskPRO\Component\Filesystem\SafeFile;
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
        'adm'        => 'backend',
        'admin'      => 'backend',
        'reports'    => 'backend',
        'api'        => 'backend',
        'agent'      => 'backend',
        'general'    => 'backend',
        'portal'     => 'user',
        'user'       => 'user',
        'helpcenter' => 'helpcenter',
    ];

    /**
     * Array of filepath => array.
     *
     * @var array
     */
    private $loadedResources = [];

    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * @var PortalBrandThemeLoader
     */
    private $brandThemeLoader;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param AppEnvInterface $appEnv
     * @param SettingsResolver $settingsResolver
     * @param PortalBrandThemeLoader $brandThemeLoader
     * @param BrandStack $brandStack
     */
    public function __construct(AppEnvInterface $appEnv, SettingsResolver $settingsResolver = null, PortalBrandThemeLoader $brandThemeLoader = null, BrandStack $brandStack = null)
    {
        $this->appEnv           = $appEnv;
        $this->settingsResolver = $settingsResolver;
        $this->brandThemeLoader = $brandThemeLoader;
        $this->brandStack       = $brandStack;
    }

    /**
     * {@inheritdoc}
     */
    public function load($groups, $language, array $loaded_phrases = null)
    {
        $lang_packs = [];

        // Always read from the default because it has the core phrases
        $lang_packs[] = DP_ROOT.'/locales/en-US';

        if ($language && $language->base_filepath && strpos($language->sys_name ?: '', 'dev_') !== 0) {
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
     * @param mixed $localeDir
     * @param mixed $name
     *
     * @return array
     */
    private function loadFile($localeDir, $name)
    {
        $base = $localeDir.DIRECTORY_SEPARATOR.$name;
        if (isset($this->loadedResources[$base])) {
            return $this->loadedResources[$base];
        }

        $allowLegacyPhrases = false;
        if ($this->settingsResolver) {
            $allowLegacyPhrases = $this->settingsResolver->getGlobalSettings()->get('settings.language.allow_legacy_phrases', false);
        }

        if ((($this->appEnv->isDebug() && !$allowLegacyPhrases) || $this->appEnv->isQa()) && $name === 'user' && $this->brandThemeLoader) {
            $theme = $this->brandThemeLoader->getPortalBrandTheme($this->brandStack->getActive()->getBrand());
            if ($theme && $theme->getActiveThemeSet()->getThemeId() === 'helpcenter') {
                if (!isset($this->loadedResources[$base])) {
                    $this->loadedResources[$base] = [];
                }

                return $this->loadedResources[$base];
            }
        }

        $relFile = basename($localeDir)."/$name.php";

        if (SafeFile::is_file("$base.php", $localeDir)) {
            $filePhrases = require "$base.php";
        } elseif (SafeFile::is_file("$base.yml", $localeDir)) {
            $filePhrases = Yaml::parse(SafeFile::file_get_contents("$base.yml", $localeDir));
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
