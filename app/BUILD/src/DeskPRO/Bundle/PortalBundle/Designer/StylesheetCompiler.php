<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Designer;

use DeskPRO\Component\Filesystem\SafeFile;
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
     * @param string $style_path Path to the file to compile
     * @param array  $variables  Array of vars to set
     * @param string $mainScss
     * @param string $customScss Custom stylesheet content
     *
     * @return string
     */
    public function compile($style_path, array $variables, $mainScss = '', $customScss = '')
    {
        $compiler = new ScssPhpCompiler();
        $project  = new SassProject();

        // Can't simply set source file and need to retrieve source as string to hack it so that scssphp can compile
        $sourceFile = realpath($style_path);
        $sourceDir  = dirname($sourceFile);
        $source     = SafeFile::file_get_contents($sourceFile, SafeFile::UNSPECIFIED);
        $source     = $this->hackScss($source, $sourceDir, $variables);
        $project->addIncludePath($sourceDir);
        $project->addIncludePath(DP_WEB_ROOT.'/pub/src');
        $project->addIncludePath(DP_WEB_ROOT.'/pub/node_modules');
        $project->setSource($source);

        // Compile custom_vars.scss from $variables
        $customVarsScss = '';
        $variables      = $this->precompileVariables($variables);

        // compiled custom css file has path like http://deskpro-dev/file.php/901TAXMZQMTDTBXXKN0/portal.css
        // so rely on this file path to prover work with sub-dirs

        $buildDir                     = DP_ACTIVE_BUILD;
        $variables['pub-path']        = "'../../assets/{$buildDir}/pub'";
        $variables['portal-res-path'] = "'../../assets/{$buildDir}/pub/src/DeskPRO/Bundle/PortalBundle/Resources'";
        $variables['portal-img-path'] = "'../../assets/{$buildDir}/pub/src/DeskPRO/Bundle/PortalBundle/Resources/img'";
        $variables['modules-path']    = "'../../assets/{$buildDir}/pub/node_modules'";

        foreach ($variables as $variable => $value) {
            if (!is_array($value)) {
                $customVarsScss .= '$'."$variable: $value;\n";
            }
        }

        $project->addFileSource("$sourceDir/".self::CUSTOM_VARS_FILENAME, $customVarsScss);

        // Set custom style contents ('custom_style.scss' and 'main.scss')
        // they could be modified via portal editor
        $project->addFileSource("$sourceDir/".self::MAIN_SCSS_FILENAME, $mainScss);
        $project->addFileSource("$sourceDir/".self::CUSTOM_SCSS_FILENAME, $customScss);

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
