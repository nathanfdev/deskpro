<?php

/**
 * DeskPRO.
 *
 * @category Controller
 */

namespace Application\DeskPRO\ResourceScanner;

class LanguagePhrases
{
    /**
     * @var string
     */
    protected $lang_root;

    public function __construct($lang_root = null)
    {
        if ($lang_root === null) {
            $lang_root = DP_ROOT.'/languages/default';
        }

        $this->lang_root = $lang_root;
    }

    public function getGroups()
    {
        $groups = [];

        $groupToReal = [
            'adm'     => 'admin',
            'admin'   => 'admin',
            'reports' => 'admin',
            'api'     => 'api',
            'agent'   => 'agent',
            'general' => 'general',
            'portal'  => 'portal',
            'user'    => 'portal',
            'reports' => 'reports',
        ];

        $lang_dir = dir($this->lang_root);
        while (($file = $lang_dir->read()) != false) {
            if ($file == '.' || $file == '..' || $file == 'export' || !is_file($lang_dir->path.'/'.$file)) {
                continue;
            }

            $phrases = require $lang_dir->path.'/'.$file;

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
