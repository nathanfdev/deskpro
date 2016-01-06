<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\PortalBundle\Designer;

use DeskPRO\Component\SassCompiler\Compiler\ScssPhpCompiler;
use DeskPRO\Component\SassCompiler\SassProject;

/**
 * Wraps up the logic for compiling portal stylesheets.
 */
class StylesheetCompiler
{
    const CUSTOM_VARS_FILENAME = 'custom_vars.scss';
    const CUSTOM_SCSS_FILENAME = 'custom_style.scss';

    /**
     * Compiles a SCSS stylesheet with custom vars and custom SCSS.
     *
     * @param string $style_path  Path to the file to compile
     * @param array  $variables   Array of vars to set
     * @param string $custom_scss Custom stylesheet content
     *
     * @return string
     */
    public function compile($style_path, array $variables = [], $custom_scss = '')
    {
        $compiler = new ScssPhpCompiler();
        $project  = new SassProject();

        // Can't simply set source file and need to retrieve source as string to hack it so that scssphp can compile
        $source_file = realpath($style_path);
        $source_dir  = dirname($source_file);
        $source      = file_get_contents($source_file);
        $source      = $this->hackScss($source, $source_dir, $variables);
        $project->setSource($source);

        // Compile custom_vars.scss from $variables
        $custom_vars_scss = '';
        $variables        = $this->precompileVariables($variables);
        foreach ($variables as $variable => $value) {
            $custom_vars_scss .= '$'."$variable: $value;\n";
        }
        $project->addFileSource("$source_dir/".self::CUSTOM_VARS_FILENAME, $custom_vars_scss);

        // Set custom_style.scss contents
        $project->addFileSource("$source_dir/".self::CUSTOM_SCSS_FILENAME, $custom_scss);

        $result = $compiler->compile($project);

        return $result;
    }

    /**
     * Pre-compiles variables into string values.
     *
     * Turns compound array values such as ['value' => 10, 'unit' => 'px'] into strings
     *
     * @param array $variables
     *
     * @return array
     */
    private function precompileVariables(array $variables)
    {
        foreach ($variables as &$variable) {
            if (is_array($variable)) {

                // compile size from value and unit parts
                if (array_key_exists('value', $variable) && array_key_exists('unit', $variable)) {
                    $variable = $variable['value'].$variable['unit'];
                }
            }
        }

        return $variables;
    }

    /**
     * Hack scss source so that leafo/scssphp lib can compile it.
     *
     * @param string $source
     * @param string $dir
     * @param array  $variables
     *
     * @return string
     */
    private function hackScss($source, $dir, array $variables)
    {
        // fix imports to absolute paths
        $source = str_replace('@import "', "@import \"$dir/", $source);

        // fix darken() with variable
        if (array_key_exists('page-background', $variables)) {
            $source = str_replace('darken($page-background', 'darken('.$variables['page-background'], $source);
        }

        return $source;
    }
}
