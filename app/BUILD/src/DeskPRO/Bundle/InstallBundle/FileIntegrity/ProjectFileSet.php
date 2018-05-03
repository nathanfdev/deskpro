<?php

namespace DeskPRO\Bundle\InstallBundle\FileIntegrity;

use DeskPRO\Component\Util\ListUtils;
use Symfony\Component\Finder\Finder;

/**
 * Builds a list of known files in the project using variable paths.
 */
class ProjectFileSet
{
    /**
     * @var \DpRun\DpEnv
     */
    private $env;

    /**
     * @var array
     */
    private $replacements;

    /**
     * ProjectFileSet constructor.
     *
     * @param \DpRun\DpEnv $env
     */
    public function __construct(\DpRun\DpEnv $env)
    {
        $this->env = $env;
    }

    /**
     * Given a path that has path vars in it, get the real path
     * based on the current enviornment.
     *
     * @param string $path
     *
     * @return string
     */
    public function getRealPath($path)
    {
        if (!$this->replacements) {
            $this->replacements = [
                'find'    => [],
                'replace' => [],
            ];

            $this->replacements['find'][]    = '%DP_DIR%';
            $this->replacements['replace'][] = $this->env->getDpRoot();

            $this->replacements['find'][]    = '%DP_APP_DIR%';
            $this->replacements['replace'][] = $this->env->getAppDir();

            $this->replacements['find'][]    = '%DP_APP_KERNEL_CACHE%';
            $this->replacements['replace'][] = $this->env->getAppBaseKernelCacheDir();

            $this->replacements['find'][]    = '%DP_APP_WWW_ASSET%';
            $this->replacements['replace'][] = $this->env->getAppWwwAssetDir();
        }

        return str_replace(
            $this->replacements['find'],
            $this->replacements['replace'],
            $path
        );
    }

    /**
     * Scans for all files in the project.
     *
     * @return array
     */
    public function buildFileSet()
    {
        $sets = [];

        //------------------------------
        // Current build files
        //------------------------------

        $finder = Finder::create()
            ->files()
            ->in($this->env->getAppDir());

        foreach ([
          $this->env->getAppDir().DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR,
          $this->env->getAppDir().DIRECTORY_SEPARATOR.'vendor-src'.DIRECTORY_SEPARATOR,
        ] as $d) {
            if (is_dir($d)) {
                $finder->exclude($d);
            }
        }

        $sets[] = $this->readIterator($finder, $this->env->getAppDir(), '%DP_APP_DIR%');
        unset($finder);

        //------------------------------
        // Vendor build files
        //------------------------------

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($this->env->getAppDir().DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR)
            ->in($this->env->getAppDir().DIRECTORY_SEPARATOR.'vendor-src'.DIRECTORY_SEPARATOR);

        $sets[] = $this->readIterator($finder, $this->env->getAppDir(), '%DP_APP_DIR%');
        unset($finder);

        //------------------------------
        // Current build kernel cache files
        //------------------------------

        $finder = Finder::create()
            ->files()
            ->in($this->env->getAppBaseKernelCacheDir());

        foreach ([
          $this->env->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.'dev'.DIRECTORY_SEPARATOR,
          $this->env->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR,
        ] as $d) {
            if (is_dir($d)) {
                $finder->exclude($d);
            }
        }

        $sets[] = $this->readIterator($finder, $this->env->getAppBaseKernelCacheDir(), '%DP_APP_KERNEL_CACHE%');
        unset($finder);

        //------------------------------
        // Asset files - built only
        //------------------------------

        $finder = Finder::create()
            ->files()
            ->in($this->env->getAppWwwAssetDir().DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR)
            ->in($this->env->getAppWwwAssetDir().DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR)
            ->in($this->env->getAppWwwAssetDir().DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'app-build'.DIRECTORY_SEPARATOR);

        $sets[] = $this->readIterator($finder, $this->env->getAppWwwAssetDir(), '%DP_APP_WWW_ASSET%');
        unset($finder);

        return ListUtils::appendListOfLists($sets);
    }

    /**
     * Checks if a path should be ignored.
     *
     * We DONT use the filters on Finder because they are converted into regex's behind the scenes,
     * and that tends to be slow and memory intensive. Easier to just do a simple string match here.
     *
     * @param string $filePath
     *
     * @return bool
     */
    private function isIgnoredFile($filePath)
    {
        static $ignoreAnywhere = [
            '/tests/',
            '/Tests/',
            '/test/',
            '/__tests__/',
            'mock',
            'Mock',
            'test-suite',
            'classes.map',
            'storybook',
        ];

        // making this static causes a syntax error in PHP5.5 on the last element of the array for some reason (wtf?)
        $ignorePaths = [
            '%DP_APP_DIR%/vendor/autoload.php',
            '%DP_APP_DIR%/vendor/behat/',
            '%DP_APP_DIR%/vendor/behatch/',
            '%DP_APP_DIR%/vendor/composer/',
            '%DP_APP_DIR%/vendor/phpmd/',
            '%DP_APP_DIR%/vendor/satooshi/php-coveralls/',
            '%DP_APP_DIR%/vendor/zircote/swagger-php/',
            '%DP_APP_DIR%/vendor/phpspec/',
            '%DP_APP_DIR%/vendor/phpdocumentor/',
            '%DP_APP_DIR%/vendor/bossa/phpspec2-expect/',
            '%DP_APP_DIR%/vendor/ocramius/proxy-manager/',
            '%DP_APP_DIR%/vendor/phpunit/phpcov/',
            '%DP_APP_DIR%/vendor/vipsoft/code-coverage-extension/',
            '%DP_APP_DIR%/vendor/squizlabs/php_codesniffer/',
            '%DP_APP_DIR%/vendor/sensiolabs/behat-page-object-extension/',
            '%DP_APP_DIR%/vendor/nelmio/api-doc-bundle/',
            '%DP_APP_DIR%/vendor/fabpot/php-cs-fixer/',
            '%DP_APP_DIR%/vendor/mockery/',
            '%DP_APP_DIR%/vendor/zendframework/zend-ldap/src/Node.php', // because we patch it ourselves
            '%DP_APP_DIR%/vendor/doctrine/orm/lib/Doctrine/ORM/UnitOfWork.php', // because we patch it ourselves
            '%DP_APP_DIR%/vendor/doctrine/orm/lib/Doctrine/ORM/Event/PreUpdateEventArgs.php', // because we patch it ourselves
            '%DP_APP_DIR%/vendor/twig/twig/lib/Twig/Node/Expression/Name.php', // because we patch it ourselves
            '%DP_APP_DIR%/vendor/twig/twig/lib/Twig/Node/Expression/NullCoalesce.php', // because we patch it ourselves
            '%DP_APP_KERNEL_CACHE%/',
            '%DP_APP_WWW_ASSET%'.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'storybook',
        ];

        foreach ($ignoreAnywhere as $p) {
            if (strpos($filePath, $p) !== false) {
                return true;
            }
        }
        foreach ($ignorePaths as $p) {
            if (strpos($filePath, $p) === 0) {
                return true;
            }
        }

        return false;
    }

    private function readIterator($iter, $base_path, $varname)
    {
        $set = [];

        /** @var \SplFileInfo $f */
        foreach ($iter as $f) {
            $path = str_replace($base_path, $varname, $f->getRealPath());
            if (!$this->isIgnoredFile($path)) {
                $set[] = $path;
            }
        }

        return $set;
    }
}
