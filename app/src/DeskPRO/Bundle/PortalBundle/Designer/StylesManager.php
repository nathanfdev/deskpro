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
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Component\SassCompiler\Compiler\ScssPhpCompiler;
use DeskPRO\Component\SassCompiler\SassProject;
use Doctrine\ORM\EntityManager;

/**
 * Class StylesManager.
 */
class StylesManager
{
    /**
     * @var string ThemeSet option name
     */
    private static $custom_vars_theme_set_option = 'custom_vars';

    /**
     * @var string DP_ROOT relative path to sassdoc parsed variables
     */
    private static $variables_json_file_path = '/../web/sassdoc/vars.json';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var PortalModeStorage
     */
    private $portal_mode_storage;

    /**
     * StylesManager constructor.
     *
     * @param EntityManager     $em
     * @param BrandStack        $brand_stack
     * @param PortalModeStorage $portal_mode_storage
     */
    public function __construct(EntityManager $em, BrandStack $brand_stack, PortalModeStorage $portal_mode_storage)
    {
        $this->em                  = $em;
        $this->brand_stack         = $brand_stack;
        $this->portal_mode_storage = $portal_mode_storage;
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
        if (!$blob_storage = $this->getCssBlobStorage()) {
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

            $blob_storage          = new BlobStorage();
            $blob_storage->blob_id = $blob->getId();
        }

        $blob_storage->data = $css;
        $this->em->persist($blob_storage);
        $this->em->flush();
    }

    /**
     * @return BlobStorage|null
     */
    public function getCssBlobStorage()
    {
        if ($blob = $this->getCssBlob()) {
            return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
        }

        return;
    }

    /**
     * @throws \Exception
     *
     * @return array Variables specs
     */
    public function getVariableGroups()
    {
        return json_decode(file_get_contents(DP_ROOT.self::$variables_json_file_path), true);
    }

    /**
     * @param bool $add_default
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getVariableValues($add_default = true)
    {
        $values = $this->getThemeSet()->getOption(self::$custom_vars_theme_set_option, []);

        if ($add_default) {
            $groups    = $this->getVariableGroups();
            $variables = call_user_func_array('array_merge', $groups);
            foreach ($variables as $variable) {
                if (!array_key_exists($variable['name'], $values)) {
                    $values[$variable['name']] = $variable['default_value'];
                }
            }
        }

        return $values;
    }

    /**
     * Commit changes of the EditThemeSet.
     */
    public function commitEditThemeSet()
    {
        $brand = $this->getBrand();
        $brand->setThemeSet($newThemeSet = $this->getEditThemeSet());
        $brand->setEditThemeSet($newEditThemeSet = $this->cloneThemeSet($newThemeSet));
        $this->em->persist($newThemeSet);
        $this->em->persist($newEditThemeSet);
        $this->em->persist($brand);
        $this->em->flush();
    }

    /**
     * Discard changes of the EditThemeSet.
     */
    public function discardEditThemeSet()
    {
        $brand = $this->getBrand();
        $brand->setEditThemeSet($newEditThemeSet = $this->cloneThemeSet($this->getThemeSet()));
        $this->em->persist($newEditThemeSet);
        $this->em->persist($brand);
        $this->em->flush();
    }

    /**
     * @throws \Exception
     * @return Blob|null
     *
     */
    private function getCssBlob()
    {
        $criteria = [
            'theme_set' => $this->isPreviewMode() ? $this->getEditThemeSet() : $this->getThemeSet(),
            'name'      => 'portal.css',
        ];
        if ($asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy($criteria)) {
            return $asset->getBlob();
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
        $source_dir = realpath(DP_ROOT.'/../pub/src/DeskPRO/Bundle/PortalBundle/Resources/style');
        $source     = file_get_contents("$source_dir/portal-style.scss");
        $source     = $this->hackScss($source, $source_dir, $variables);
        $project->setSource($source);

        // Compile custom_vars.scss from $variables
        $custom_vars_scss = '';
        foreach ($variables as $variable => $value) {
            $custom_vars_scss .= '$'."$variable: $value;\n";
        }
        $project->addFileSource("$source_dir/custom_vars.scss", $custom_vars_scss);

        $result = $compiler->compile($project);

        return $result;
    }

    /**
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    private function getThemeSet()
    {
        if (!$themeSet = $this->getBrand()->getThemeSet()) {
            throw new \Exception('Unable to resolve a theme set');
        }

        return $themeSet;
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
        $source = str_replace('darken($page-background', 'darken('.$variables['page-background'], $source);

        return $source;
    }

    /**
     * @param ThemeSet $themeSet
     *
     * @return ThemeSet
     */
    private function cloneThemeSet(ThemeSet $themeSet)
    {
        $clone = new ThemeSet();
        $clone->setThemeId($themeSet->getThemeId());
        $clone->setOptions($themeSet->getOptions());

        return $clone;
    }

    /**
     * @return bool
     */
    private function isPreviewMode()
    {
        return $this->portal_mode_storage->getMode()->isAdminPreview();
    }
}
