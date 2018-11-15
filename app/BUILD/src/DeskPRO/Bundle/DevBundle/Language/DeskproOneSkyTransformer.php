<?php

namespace DeskPRO\Bundle\DevBundle\Language;

use DeskPRO\Component\Filesystem\TmpDir;
use DeskPRO\Component\Util\StringUtils;
use Symfony\Component\Yaml\Yaml;

/**
 * Converts our local YML lang files to a YML file OneSky will handle.
 *
 * This is necessary because we need to use "Ruby YML" at OneSky to
 * get proper plural handling. Normal YML files do not have plural handling
 * which makes for a poor translation experience.
 */
class DeskproOneSkyTransformer
{
    private $info;
    private $origDir;
    private $tmpDir;
    private $donePaths = [];

    /**
     * OneSkyTransformer constructor.
     *
     * @param $locale
     * @param $origDir
     * @param $tmpDir
     */
    public function __construct($origDir, $baseTmpDir = null)
    {
        $this->origDir = $origDir;
        $this->tmpDir  = TmpDir::makeTmpDir($baseTmpDir);
        $this->info    = $this->origFileData('localeInfo.yml');
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function deskproLangFileToOneSky($name)
    {
        if (isset($this->donePaths[$name])) {
            return $this->donePaths[$name];
        }
        $data    = $this->origFileData($name);
        $locale  = $this->info['locale'];
        $tmpPath = $this->tmpDir.DIRECTORY_SEPARATOR.$name;

        file_put_contents($tmpPath, Yaml::dump([$locale => $data], 3, 2));

        return $this->donePaths[$name] = $tmpPath;
    }

    /**
     * @param array $oneskyData
     *
     * @return array
     */
    public function oneSkyDataToDeskproData(array $oneskyData)
    {
        $d = array_values($oneskyData);

        return $d[0] ?: [];
    }

    /**
     * @param string $yamlString
     *
     * @return array
     */
    public function parseOneSkyYamlString($yamlString)
    {
        // OneSky mangles newlines in RTL langs like ar
        $y = str_replace("\\n\\\n", '\\n', trim($yamlString));

        // OneSky puts everything under a parent key being the lang
        // locale. But this also fails on 'no' for Norwegian because
        // they erroneously encode it as:
        // ---
        // ? "no"
        // :
        //   phrases.here: xxx

        // So we're just going to discard the leading lines until we get
        // to the first phrase, then de-indent the rest of the lines
        // so it becomes just a big top-level map.

        $m = null;
        if (!preg_match('#^\s+#m', $y, $m, \PREG_OFFSET_CAPTURE)) {
            throw new \InvalidArgumentException('Couldnt find where phrases start');
        }

        $indentString   = $m[0][0];
        $rootYamlString = substr($y, $m[0][1]);

        $rootYamlString = StringUtils::mapLines($rootYamlString, function ($line) use ($indentString) {
            $newLine = StringUtils::removeFromStart($indentString, $line);
            if ($newLine === null) {
                throw new \InvalidArgumentException("Invalid line: $line");
            }

            return $newLine;
        });

        return Yaml::parse($rootYamlString);
    }

    /**
     * @param string $name
     * @param bool   $ignoreMissing
     *
     * @return array
     */
    private function origFileData($name, $ignoreMissing = false)
    {
        $path = $this->origDir.DIRECTORY_SEPARATOR.$name;
        if (!is_file($path)) {
            if ($ignoreMissing) {
                return [];
            }
            throw new \InvalidArgumentException("No such file: $name");
        }

        return Yaml::parse(file_get_contents($path));
    }
}
