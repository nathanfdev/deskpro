<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\SassCompiler\ScssPhp;

use Leafo\ScssPhp\Compiler as BaseCompiler;
use Leafo\ScssPhp\Parser as ScssPhpParser;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This is an extension to the ScssPhp to add:.
 *
 * - Ability to "jail" to specific paths. E.g., makes it impossible to try and @import(/etc/passwd) or something.
 * - And better erorr handling and choice of what happens when something goes wrong. Eg. what happens if
 *   you try to @import a file that doesnt exist. Defaults to just addin a CSS comment.
 */
class Compiler extends BaseCompiler
{
    // note: charset NOT being utf8 here is important
    // utf8 with the SCSS parser *significantly* reduces performance
    const FILE_ENCODING = 'ISO-8895-1';

    /**
     * @var FileLoaderInterface[]
     */
    private $file_loaders = [];

    /**
     * @var FileLocatorInterface[]
     */
    private $file_locators = [];

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        parent::__construct();
        $this->options = self::getOptionsResolver()->resolve($options ?: []);

        $this->setEncoding(self::FILE_ENCODING);

        foreach ($this->options['file_loaders'] as $fl) {
            if (!$fl instanceof FileLoaderInterface) {
                throw new InvalidOptionsException();
            }
            $this->file_loaders[] = $fl;
        }

        foreach ($this->options['file_locators'] as $fl) {
            if (!$fl instanceof FileLocatorInterface) {
                throw new InvalidOptionsException();
            }
            $this->file_locators[] = $fl;
        }

        foreach ($this->options['include_paths'] as $p) {
            $this->addImportPath($p);
        }
    }

    /**
     * @param string $url
     *
     * @return null|string
     */
    public function findImport($url)
    {
        foreach ($this->file_locators as $fl) {
            $p = $fl->locateFile($url);
            if ($p) {
                return $p;
            }
        }

        // Manually locate plain CSS files cause BaseCompiler doesnt handle them properly
        if (preg_match('/^\.\/.*?\.css$/', $url) || preg_match('/^[^\/\\\].*?\.css$/', $url)) {
            foreach ($this->importPaths as $pathOption) {
                $path = $this->normalizeImportPath($url, $pathOption);
                $p    = realpath($path.DIRECTORY_SEPARATOR.$url);
                if ($p && is_file($p)) {
                    return $p;
                }
            }
        }

        $p = parent::findImport($url);
        if ($p) {
            return $p;
        }

        return $url;
    }

    /**
     * @param string $filePath
     * @param string $path
     *
     * @return string
     */
    private function normalizeImportPath($filePath, $path)
    {
        if (is_callable($path)) {
            return $path($filePath);
        }

        return $path;
    }

    /**
     * @return OptionsResolver
     */
    private static function getOptionsResolver()
    {
        static $resolver;

        if (!$resolver) {
            $resolver = new OptionsResolver();

            // What happens when all file loaders cant load it
            $resolver->setDefault('invalid_load_file', 'comment');
            $resolver->setAllowedValues('invalid_load_file', ['ignore', 'throw', 'comment']);

            // What happens if there's an exception thrown during load of a file
            $resolver->setDefault('error_load_file', 'comment');
            $resolver->setAllowedValues('error_load_file', ['ignore', 'throw', 'comment', 'continue']);

            $resolver->setDefault('file_loaders', function () {
                return new DefaultFileLoader();
            });

            $resolver->setDefault('include_paths', []);
            $resolver->setDefault('file_locators', []);
        }

        return $resolver;
    }

    /**
     * Attempts to load a file through all our loaders.
     *
     * @param string $path
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function loadFile($path)
    {
        foreach ($this->file_loaders as $fl) {
            try {
                $f = $fl->loadFile($path);
                if ($f !== null) {
                    return $f;
                }
            } catch (\Exception $e) {
                switch ($this->options['error_load_file']) {
                    case 'throw':
                        throw $e;
                    case 'comment':
                        return '/* ERROR LOADING FILE ('.$path.'): '.$e->getMessage().' */';
                    case 'ignore':
                        return '';
                }
            }
        }

        switch ($this->options['invalid_load_file']) {
            case 'throw':
                throw new \InvalidArgumentException('Could not load file: '.$path);
            case 'comment':
                return '/* COULD NOT LOAD FILE ('.$path.') */';
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function importFile($path, $out)
    {
        // see if tree is cached
        $realPath = @realpath($path);

        if (isset($this->importCache[$realPath])) {
            $tree = $this->importCache[$realPath];
        } elseif (isset($this->importCache[$path])) {
            $tree = $this->importCache[$path];
        } else {
            $code = $this->loadFile($path);

            $parser = new ScssPhpParser($path, 0, self::FILE_ENCODING);
            $tree   = $parser->parse($code);

            if ($realPath) {
                $this->importCache[$realPath] = $tree;
            } else {
                $this->importCache[$path] = $tree;
            }
        }

        $pi = pathinfo($path);
        array_unshift($this->importPaths, $pi['dirname']);
        $this->compileChildrenNoReturn($tree->children, $out);
        array_shift($this->importPaths);
    }
}
