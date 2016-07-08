<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\DevBundle\Language;

use DeskPRO\Component\Util\MapUtils;

class LangPhpFileCompiler
{
    private static $groupFileMap = [
        'adm'     => 'admin.php',
        'admin'   => 'admin.php',
        'api'     => 'api.php',
        'agent'   => 'agent.php',
        'general' => 'general.php',
        'portal'  => 'portal.php',
        'user'    => 'portal.php',
    ];

    /**
     * Given a phrase name, get the filename it should be included in.
     *
     * Example: foo.bar.baz -> foo/bar.php
     *
     * @param string $phraseName
     *
     * @return string
     */
    public function getFilenameFromPhraseName($phraseName)
    {
        $parts = explode('.', $phraseName, 3);

        return isset(self::$groupFileMap[$parts[0]]) ? self::$groupFileMap[$parts[0]] : self::$groupFileMap['general'];
    }

    /**
     * @param array $phrases
     *
     * @return string
     */
    public function compilePhpCode(array $phrases)
    {
        $phrases = MapUtils::filterOutValues($phrases, ['', false, null], true);
        ksort($phrases, \SORT_STRING);

        $maxLen = 0;
        foreach ($phrases as $phraseId => $string) {
            $maxLen = max($maxLen, strlen($phraseId));
        }

        $maxLen += 2;

        $php   = ["<?php\n\n"];
        $php[] = $this->getFileHeader();
        $php[] = "\n\n";
        $php[] = "return array(\n";

        ksort($phrases, \SORT_STRING);

        foreach ($phrases as $phraseId => $string) {
            $php[] = sprintf("    %-{$maxLen}s => %s,\n", "'$phraseId'", var_export($string, true));
        }

        $php[] = ");\n";

        $php = implode('', $php);

        return $php;
    }

    private function getFileHeader()
    {
        return <<<'HEADER'
/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
HEADER;
    }
}
