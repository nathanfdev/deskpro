<?php

/**
 * DeskPRO.
 *
 * @category Controller
 */

namespace Application\DeskPRO\ResourceScanner;

use DeskPRO\Component\Util\MapUtils;
use Symfony\Component\Yaml\Yaml;

class LanguagePhrases
{
    /**
     * @var string
     */
    protected $lang_root;

    public function __construct($lang_root = null)
    {
        if ($lang_root === null) {
            $lang_root = DP_ROOT.'/locales/en-US';
        }

        $this->lang_root = $lang_root;
    }

    public function getGroups()
    {
        $groups = [];

        $groupToReal = [
            'adm'     => 'backend',
            'admin'   => 'backend',
            'reports' => 'backend',
            'api'     => 'backend',
            'agent'   => 'backend',
            'general' => 'backend',
            'portal'  => 'user',
            'user'    => 'user',
            'reports' => 'backend',
        ];

        $lang_dir = dir($this->lang_root);
        while (($file = $lang_dir->read()) != false) {
            if ($file == '.' || $file == '..' || $file == 'export' || !is_file($lang_dir->path.'/'.$file)) {
                continue;
            }

            if (strpos($file, '.php')) {
                $phrases = require $lang_dir->path.'/'.$file;
            } elseif (strpos($file, '.yml')) {
                $phrases = MapUtils::flattenKeys(Yaml::parse(file_get_contents($lang_dir->path.'/'.$file)));
            } else {
                continue;
            }

            foreach ($phrases as $phraseId => $x) {
                $parts = explode('.', $phraseId);
                if (!isset($groupToReal[$parts[0]])) {
                    continue;
                }
                $realGroupId = $groupToReal[$parts[0]];
                $file        = isset($parts[2]) ? $parts[1] : $realGroupId;

                if (!isset($groups[$realGroupId])) {
                    $groups[$realGroupId] = [];
                }
                if (!in_array($file, $groups[$realGroupId])) {
                    $groups[$realGroupId][] = $file;
                }
            }
        }

        return $groups;
    }
}
