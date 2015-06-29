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

namespace DeskPRO\Bundle\PortalBundle\Brand\Style;

use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Component\SassCompiler\SassCompiler;
use DeskPRO\Component\SassCompiler\SassProject;

class StyleCompiler
{
    /**
     * @var SassCompiler
     */
    private $sass_compiler;

    /**
     * @param SassCompiler $sass_compiler
     */
    function __construct(SassCompiler $sass_compiler)
    {
        $this->sass_compiler = $sass_compiler;
    }

    /**
     * @param BrandContainer $brand_container
     * @return string
     */
    public function compileBrandStyle(BrandContainer $brand_container)
    {
        $proj = new SassProject();
        $proj->addIncludePath($brand_container->getTheme()->getStylesheetsPath());
        $proj->addIncludePath(DP_WEB_ROOT . '/pub/node_modules');

        $styles = $brand_container->getAssetLoader()->getStylesheets();
        foreach ($styles as $f => $src) {
            if ($f === 'main.scss') {
                $proj->setSource($src);
            } else {
                $proj->addFileSource($f, $src);
            }
        }

        // if we arent overriding it, we load the original
        if (!isset($styles['main.scss'])) {
            $proj->setSource(file_get_contents($brand_container->getTheme()->getStylesheetsPath() . DIRECTORY_SEPARATOR . 'main.scss'));
        }

        return $this->sass_compiler->compileProject($proj);
    }
}