<?php

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
    const MAIN_SCSS_FILENAME   = 'main.scss';
    const CUSTOM_VARS_FILENAME = 'custom_vars.scss';
    const CUSTOM_SCSS_FILENAME = 'custom_style.scss';

    /**
     * Compiles a SCSS stylesheet with custom vars and custom SCSS.
     *
     * @param string $style_path  Path to the file to compile
     * @param array  $variables   Array of vars to set
     * @param string $mainScss
     * @param string $custom_scss Custom stylesheet content
     *
     * @return string
     */
    public function compile($style_path, array $variables, $mainScss, $custom_scss = '')
    {
        $compiler = new ScssPhpCompiler();
        $project  = new SassProject();

        // Can't simply set source file and need to retrieve source as string to hack it so that scssphp can compile
        $source_file = realpath($style_path);
        $source_dir  = dirname($source_file);
        $source      = file_get_contents($source_file);
        $source      = $this->hackScss($source, $source_dir, $variables);
        $project->addIncludePath($source_dir);
        $project->addIncludePath(DP_WEB_ROOT.'/pub/src');
        $project->addIncludePath(DP_WEB_ROOT.'/pub/node_modules');
        $project->setSource($source);

        // Compile custom_vars.scss from $variables
        $custom_vars_scss = '';
        $variables        = $this->precompileVariables($variables);

        // compiled custom css file has path like http://deskpro-dev/file.php/901TAXMZQMTDTBXXKN0/portal.css
        // so rely on this file path to prover work with sub-dirs

        $buildDir                     = DP_ACTIVE_BUILD;
        $variables['pub-path']        = "'../../assets/{$buildDir}/pub'";
        $variables['portal-res-path'] = "'../../assets/{$buildDir}/pub/src/DeskPRO/Bundle/PortalBundle/Resources'";
        $variables['portal-img-path'] = "'../../assets/{$buildDir}/pub/src/DeskPRO/Bundle/PortalBundle/Resources/img'";
        $variables['modules-path']    = "'../../assets/{$buildDir}/pub/node_modules'";

        foreach ($variables as $variable => $value) {
            $custom_vars_scss .= '$'."$variable: $value;\n";
        }

        $project->addFileSource("$source_dir/".self::CUSTOM_VARS_FILENAME, $custom_vars_scss);

        // Set custom style contents ('custom_style.scss' and 'main.scss')
        // they could be modified via portal editor
        $project->addFileSource("$source_dir/".self::MAIN_SCSS_FILENAME, $mainScss);
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
        // fix darken() with variable
        if (array_key_exists('page-background', $variables)) {
            $source = str_replace('darken($page-background', 'darken('.$variables['page-background'], $source);
        }

        return $source;
    }
}
