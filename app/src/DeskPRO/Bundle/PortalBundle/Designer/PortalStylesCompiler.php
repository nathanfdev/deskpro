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

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\BlobStorage;
use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Component\SassCompiler\Compiler\ScssPhpCompiler;
use DeskPRO\Component\SassCompiler\SassProject;
use Doctrine\ORM\EntityManager;

/**
 * Class PortalStylesCompiler.
 *
 * Re-compiles portal styles into the current brand's edit theme set
 */
class PortalStylesCompiler
{
    /**
     * @var string ThemeSet option name
     */
    public static $custom_vars_theme_set_option = 'custom_vars';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var string DP_ROOT relative or full path to the portal SCSS file
     */
    private $styles_file_path;

    /**
     * @var string
     */
    private $custom_vars_file_name;

    /**
     * @var string
     */
    private $custom_scss;

    /**
     * @var string
     */
    private $custom_scss_file_name;

    /**
     * @param EntityManager $em
     * @param BrandStack    $brand_stack
     * @param string        $styles_file_path
     * @param string        $custom_vars_file_name
     * @param string        $custom_scss
     * @param string        $custom_scss_file_name
     */
    public function __construct(
        EntityManager $em,
        BrandStack $brand_stack,
        $styles_file_path,
        $custom_vars_file_name,
        $custom_scss,
        $custom_scss_file_name
    ) {
        $this->em                    = $em;
        $this->brand_stack           = $brand_stack;
        $this->styles_file_path      = $styles_file_path;
        $this->custom_vars_file_name = $custom_vars_file_name;
        $this->custom_scss           = $custom_scss;
        $this->custom_scss_file_name = $custom_scss_file_name;

        // try to resolve path in constructor to get early Exception if path isn't valid
        $this->getStylePath();
    }

    /**
     * Recompile portal css.
     *
     * @param array $variables
     *
     * @throws \Exception
     */
    public function recompile(array $variables)
    {
        $themeSet = $this->getEditThemeSet();
        $themeSet->setOption(self::$custom_vars_theme_set_option, $variables);

        $this->em->persist($themeSet);
        $this->em->flush();

        $css = $this->compileCss($variables);

        // Find existing or create a new blob storage for the custom Css
        if (!$blob_storage = $this->getEditThemeSetCssBlobStorage()) {
            $blob = new Blob();
            $blob->setFilename('portal.css');
            $blob->blob_hash = md5($css);
            $this->em->persist($blob);
            $this->em->flush();

            $asset = new ThemeSetAsset();
            $asset->setName('portal.css');
            $asset->setThemeSet($themeSet);
            $asset->setTags(['portal_css']);
            $asset->setBlob($blob);
            $this->em->persist($asset);
            $this->em->flush();
        } else {
            // Saving in a new BlobStorage instance to use its' $id as CSS version to bypass caches
            $blob = $this->getEditThemeSetCssBlob();
            $this->em->remove($blob_storage);
            $this->em->flush();
        }

        $blob_storage          = new BlobStorage();
        $blob_storage->blob_id = $blob->getId();
        $blob_storage->data    = $css;
        $this->em->persist($blob_storage);
        $this->em->flush();
    }

    /**
     * @return BlobStorage|null
     */
    public function getEditThemeSetCssBlobStorage()
    {
        if ($blob = $this->getEditThemeSetCssBlob()) {
            return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
        }

        return;
    }

    /**
     * Compile portal css from scss sources.
     *
     * @param array $variables
     *
     * @return string
     */
    private function compileCss(array $variables)
    {
        $compiler = new ScssPhpCompiler();
        $project  = new SassProject();

        // Can't simply set source file and need to retrieve source as string to hack it so that scssphp can compile
        $source_file = realpath($this->getStylePath());
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
        $project->addFileSource("$source_dir/{$this->custom_vars_file_name}", $custom_vars_scss);

        // Set custom_style.scss contents
        $project->addFileSource("$source_dir/{$this->custom_scss_file_name}", $this->custom_scss);

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

    /**
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    private function getEditThemeSet()
    {
        if (!$themeSet = $this->getBrand()->getEditThemeSet()) {
            $themeSet = $this->cloneThemeSet($this->getThemeSet());
            $brand    = $this->getBrand();
            $brand->setEditThemeSet($themeSet);
            $this->em->persist($themeSet);
            $this->em->persist($brand);
            $this->em->flush();
        }

        return $themeSet;
    }

    /**
     * @throws \Exception
     *
     * @return Blob|null
     */
    private function getEditThemeSetCssBlob()
    {
        $criteria = [
            'theme_set' => $this->getEditThemeSet(),
            'name'      => 'portal.css',
        ];
        if ($asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy($criteria)) {
            return $asset->getBlob();
        }

        return;
    }

    /**
     * @throws \Exception
     *
     * @return Brand
     */
    private function getBrand()
    {
        if (!$container = $this->brand_stack->getActive()) {
            throw new \Exception('Unable to resolve the current brand');
        }

        return $container->getBrand();
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    private function getStylePath()
    {
        if (file_exists($this->styles_file_path)) {
            return $this->styles_file_path;
        } else {
            $path = DP_ROOT.'/'.rtrim($this->styles_file_path, '/');
            if (file_exists($path)) {
                return $path;
            } else {
                throw new \Exception("Can't resolve a file from the given path: {$this->styles_file_path}");
            }
        }
    }
}
