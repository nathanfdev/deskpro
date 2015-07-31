<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\SassCompiler\ScssPhp;

use Leafo\ScssPhp\Compiler as BaseCompiler;
use Leafo\ScssPhp\Parser as ScssPhpParser;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This is an extension to the ScssPhp to add:
 *
 * - Ability to "jail" to specific paths. E.g., makes it impossible to try and @import(/etc/passwd) or something.
 * - And better erorr handling and choice of what happens when something goes wrong. Eg. what happens if
 *   you try to @import a file that doesnt exist. Defaults to just addin a CSS comment.
 */
class Compiler extends BaseCompiler
{
    /**
     * @var FileLoaderInterface[]
     */
    private $file_loaders = array();

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     * @param FileLoaderInterface[] $file_loaders
     */
    function __construct(array $options = null)
    {
        $this->options = self::getOptionsResolver()->resolve($options ?: array());

        foreach ($this->options['file_loaders'] as $fl) {
            if (!$fl instanceof FileLoaderInterface) {
                throw new InvalidOptionsException();
            }
            $this->file_loaders[] = $fl;
        }

        foreach ($this->options['include_paths'] as $p) {
            $this->addImportPath($p);
        }
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
            $resolver->setAllowedValues('invalid_load_file', array('ignore', 'throw', 'comment'));

            // What happens if there's an exception thrown during load of a file
            $resolver->setDefault('error_load_file', 'comment');
            $resolver->setAllowedValues('error_load_file', array('ignore', 'throw', 'comment', 'continue'));

            $resolver->setDefault('file_loaders', function(Options $options) {
                return new DefaultFileLoader();
            });

            $resolver->setDefault('include_paths', array());
        }

        return $resolver;
    }

    /**
     * Attempts to load a file through all our loaders.
     *
     * @param string $path
     * @return string
     * @throws \Exception
     */
    protected function loadFile($path)
    {
        foreach ($this->file_loaders as $fl) {
            try {
                $f = $fl->loadFile($path);
            } catch (\Exception $e) {
                switch ($this->options['error_load_file']) {
                    case 'throw':
                        throw $e;
                    case 'comment':
                        return '/* ERROR LOADING FILE (' . $path . '): ' . $e->getMessage() . ' */';
                    case 'ignore':
                        return '';
                }
            }
            if ($f !== null) {
                return $f;
            }
        }

        switch ($this->options['invalid_load_file']) {
            case 'throw':
                throw new \InvalidArgumentException('Could not load file: ' . $path);
            case 'comment':
                return '/* COULD NOT LOAD FILE (' . $path . '): ' . $e->getMessage() . ' */';
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function importFile($path, $out)
    {
        // see if tree is cached
        $realPath = realpath($path);

        if (isset($this->importCache[$realPath])) {
            $tree = $this->importCache[$realPath];
        } else {
            $code = $this->loadFile($path);

            $parser = new ScssPhpParser($path, false);
            $tree = $parser->parse($code);
            $this->importCache[$realPath] = $tree;
        }

        $pi = pathinfo($path);
        array_unshift($this->importPaths, $pi['dirname']);
        $this->compileChildren($tree->children, $out);
        array_shift($this->importPaths);
    }
}
