<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 */

namespace DeskPRO\Component\SassCompiler\Compiler;

use DeskPRO\Component\SassCompiler\SassProject;
use DeskPRO\Component\SassCompiler\ScssPhp\Compiler;
use DeskPRO\Component\SassCompiler\ScssPhp\DefaultFileLoader;
use DeskPRO\Component\SassCompiler\ScssPhp\StringFileLoader;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScssPhpCompiler implements CompilerInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        $this->options = self::getOptionsResolver()->resolve($options ?: []);
    }

    /**
     * @return OptionsResolver
     */
    private static function getOptionsResolver()
    {
        static $resolver;

        if (!$resolver) {
            $resolver = new OptionsResolver();
            $resolver->setDefault('compiler_options', [
                'error_load_file' => 'throw',
            ]);
        }

        return $resolver;
    }

    /**
     * @param SassProject $project
     *
     * @return Compiler
     */
    protected function createCompilerForProject(SassProject $project)
    {
        $compiler_options                  = $this->options['compiler_options'];
        $compiler_options['include_paths'] = $project->getIncludePaths();

        $file_sources  = $project->getFileSources();
        $string_loader = new StringFileLoader($file_sources);

        $compiler_options['file_loaders'] = [
            $string_loader,
            new DefaultFileLoader($project->getIncludePaths()),
        ];
        $compiler_options['file_locators'] = [
            $string_loader,
        ];

        $compiler = new Compiler($compiler_options);
        $compiler->addImportPath(function ($f) use ($file_sources) {
            return isset($file_sources[$f]) ? $f : null;
        });

        return $compiler;
    }

    /**
     * @param SassProject $project
     *
     * @return string
     */
    public function compile(SassProject $project)
    {
        $compiler = $this->createCompilerForProject($project);

        return $compiler->compile($project->getSource());
    }
}
