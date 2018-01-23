<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
