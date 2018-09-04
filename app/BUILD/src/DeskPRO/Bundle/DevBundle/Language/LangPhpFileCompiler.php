<?php

namespace DeskPRO\Bundle\DevBundle\Language;

use DeskPRO\Component\Util\MapUtils;

class LangPhpFileCompiler
{
    /**
     * @var bool
     */
    private $oldStyleArray = false;

    private static $groupFileMap = [
        'adm'     => 'agent.php',
        'admin'   => 'agent.php',
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
     * Enable old style PHP arrays. This is needed by OneSky.
     */
    public function enableOldStyleArray()
    {
        $this->oldStyleArray = true;
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
        if ($this->oldStyleArray) {
            $php[] = "return array(\n";
        } else {
            $php[] = "return [\n";
        }

        ksort($phrases, \SORT_STRING);

        foreach ($phrases as $phraseId => $string) {
            $php[] = sprintf("    %-{$maxLen}s => %s,\n", "'$phraseId'", var_export($string, true));
        }

        if ($this->oldStyleArray) {
            $php[] = ");\n";
        } else {
            $php[] = "];\n";
        }

        $php = implode('', $php);

        return $php;
    }

    private function getFileHeader()
    {
        return '';
    }
}
