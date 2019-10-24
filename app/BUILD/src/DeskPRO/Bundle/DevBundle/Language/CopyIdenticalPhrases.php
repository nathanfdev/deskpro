<?php

namespace DeskPRO\Bundle\DevBundle\Language;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CopyIdenticalPhrases
 *
 * @package DeskPRO\Bundle\DevBundle\Language
 */
class CopyIdenticalPhrases
{
    /**
     * Group keys
     */
    const GROUP_PREFIX = 'prefix';
    const GROUP_NON_PREFIX = 'non_prefix';

    /**
     * Find the same phrases within the root language (ID prefix vs. non ID prefix)
     *
     * @param string   $localesDir
     * @param string   $rootLanguage
     * @param string[] $languageFiles
     * @param string   $idPrefix
     * @param bool     $isMatchingOnPhrase
     * @return array
     */
    public function analyze($localesDir, $rootLanguage, array $languageFiles, $idPrefix, $isMatchingOnPhrase)
    {
        $same = [];
        $rootLanguageDirPath = $this->buildRootLanguageDirPath($localesDir, $rootLanguage);

        foreach ($languageFiles as $languageFile) {
            $languageFilepath = $rootLanguageDirPath.DIRECTORY_SEPARATOR.$languageFile;

            if (!is_readable($languageFilepath)) {
                throw new \RuntimeException("Language file path {$languageFilepath} is not readable");
            }

            $phrases = Yaml::parse(file_get_contents($languageFilepath));

            // Hash the values for comparison
            if ($isMatchingOnPhrase) {
                // Match on phrase
                $values = array_map(function ($value) {
                    return [$value, md5(serialize($value))];
                }, $phrases);
            } else {
                // Match on ID postfix
                $values = array_reduce(array_keys($phrases), function (array $list, $key) use ($phrases) {
                    return array_merge($list, [$key => [$phrases[$key], md5($this->onlyPhraseIdPostfix($key))]]);
                }, []);
            }

            // Split into ID prefix/non ID prefix groups
            $groups = [
                self::GROUP_PREFIX => [],
                self::GROUP_NON_PREFIX => [],
            ];
            foreach ($values as $key => $value) {
                if (preg_match("/^{$idPrefix}/", $key)) {
                    $groups[self::GROUP_PREFIX][$key] = $value;
                } else {
                    $groups[self::GROUP_NON_PREFIX][$key] = $value;
                }
            }

            // Find the same phrases in non ID prefix group (Big O goes out the window here...)
            foreach ($groups[self::GROUP_PREFIX] as $prefixKey => $prefixValue) {
                foreach ($groups[self::GROUP_NON_PREFIX] as $nonPrefixKey => $nonPrefixValue) {
                    if ($prefixValue[1] === $nonPrefixValue[1]) {
                        $same[$languageFile][$prefixValue[1]] = [$nonPrefixKey, $prefixKey, $nonPrefixValue[0]];
                    }
                }
            }
        }

        return $same;
    }

    /**
     * @param array  $same
     * @param string $localesDir
     * @param string $rootLanguage
     * @return array
     */
    public function buildAdditions(array $same, $localesDir, $rootLanguage)
    {
        $additions = [];
        $languageDirs = $this->getOtherLanguageDirPaths($localesDir, $rootLanguage);

        foreach ($same as $languageFile => $replacementTuples) {
            foreach ($languageDirs as $languageDir) {
                $languageFilepath = $languageDir.DIRECTORY_SEPARATOR.$languageFile;

                if (!is_readable($languageFilepath)) {
                    continue;
                }

                $phrases = Yaml::parse(file_get_contents($languageFilepath));

                if (!is_array($phrases)) {
                    continue;
                }

                foreach ($replacementTuples as $hash => $replacement) {
                    foreach ($phrases as $id => $phrase) {
                        if ($replacement[0] === $id) {
                            $additions[$languageFilepath][$replacement[1]] = $phrase;
                        }
                    }
                }
            }
        }

        return $additions;
    }

    /**
     * @param array         $additions
     * @param callable|null $afterPrepend
     * @param callable|null $onFailure
     */
    public function prependAdditions(array $additions, callable $afterPrepend = null, callable $onFailure = null)
    {
        foreach ($additions as $languageFile => $phrases) {
            if (!is_writable($languageFile)) {
                if ($onFailure) {
                    $onFailure($languageFile);
                }
            }

            $existing = Yaml::parse(file_get_contents($languageFile));
            $existing = array_merge($phrases, $existing);
            file_put_contents($languageFile, Yaml::dump($existing));

            if ($afterPrepend) {
                $afterPrepend($languageFile);
            }
        }
    }

    /**
     * @param string $localesDir
     * @param string $rootLanguage
     * @return array
     */
    private function getOtherLanguageDirPaths($localesDir, $rootLanguage)
    {
        $finder = (new Finder())
            ->in($localesDir)
        ;

        $paths = array_map(function (SplFileInfo $dir) {
            return $dir->getFilename();
        }, iterator_to_array($finder->directories()));

        $paths = array_filter($paths, function ($name) use ($rootLanguage) {
            return ($name != $rootLanguage);
        });

        return array_keys($paths);
    }

    /**
     * @param string $localesDir
     * @param string $rootLanguage
     * @return string
     */
    private function buildRootLanguageDirPath($localesDir, $rootLanguage)
    {
        return $localesDir.DIRECTORY_SEPARATOR.$rootLanguage;
    }

    /**
     * @param string $id
     * @return string
     */
    private function onlyPhraseIdPostfix($id)
    {
        return preg_replace('/^[a-z]*\./', '', $id);
    }
}
