<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

/**
 * DeskPRO.
 *
 * @category Translate
 */

namespace Application\DeskPRO\Translate\Loader;

use DpSys\CodePlugin\DpPlugins;
use Orb\Util\Arrays;

/**
 * Loads default phrases from filesystem-based lang packs.
 */
class SystemLoader implements LoaderInterface
{
    private static $groupFileMap = [
        'adm'     => 'admin.php',
        'admin'   => 'admin.php',
        'reports' => 'admin.php',
        'api'     => 'api.php',
        'agent'   => 'agent.php',
        'general' => 'general.php',
        'portal'  => 'portal.php',
        'user'    => 'portal.php',
    ];

    /**
     * Array of filepath => array.
     *
     * @var array
     */
    protected $loaded_files = [];

    /**
     * {@inheritdoc}
     */
    public function load($groups, $language, array $loaded_phrases = null)
    {
        $lang_packs = [];

        // Always read from the default because it has the core phrases
        $lang_packs[] = DP_ROOT.'/languages/default';

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
                    $file        = $path.'/'.self::$groupFileMap[$groupParts[0]];
                    $filePhrases = $this->loadFile($file);
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
    public function loadFile($file)
    {
        if (isset($this->loaded_files[$file])) {
            return $this->loaded_files[$file];
        }

        if (is_file($file)) {
            $relFile     = basename(dirname($file)).'/'.basename($file);
            $filePhrases = include $file;
            if ($filePhrases && is_array($filePhrases)) {
                $filePhrases = array_merge(
                    $filePhrases,
                    DpPlugins::getManager()->loadExtraLangFile($relFile)
                );

                foreach ($filePhrases as $phraseName => $phraseTranslation) {
                    $groupParts = explode('.', $phraseName, 3);

                    $this->loaded_files[$file][$groupParts[0]][$groupParts[1]][$phraseName] = $phraseTranslation;
                }
            }
        }

        if (!isset($this->loaded_files[$file])) {
            $this->loaded_files[$file] = [];
        }

        return $this->loaded_files[$file];
    }
}
