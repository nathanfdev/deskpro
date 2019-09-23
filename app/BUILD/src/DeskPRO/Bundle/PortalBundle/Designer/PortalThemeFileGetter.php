<?php

namespace DeskPRO\Bundle\PortalBundle\Designer;

use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;

class PortalThemeFileGetter
{
    /**
     * @var ThemeSet
     */
    private $themeSet;

    /**
     * @var ThemeSet
     */
    private $editThemeSet;

    private $assetDir;

    public function __construct(
        ThemeSet $themeSet,
        ThemeSet $editThemeSet,
        $assetDir
    ) {
        $this->themeSet     = $themeSet;
        $this->editThemeSet = $editThemeSet;
        $this->assetDir     = $assetDir;
    }

    public function getVarsJson()
    {
        if ($this->editThemeSet->getThemeId() === 'helpcenter') {
            return $this->assetDir.'/web/sassdoc/helpcenter_vars.json';
        }

        return $this->assetDir.'/web/sassdoc/vars.json';
    }

    public function getPortalStylePath($direction = 'LTR')
    {
        if ($this->editThemeSet->getThemeId() === 'helpcenter') {
            return $this->assetDir.'/pub/src/DeskPRO/Bundle/PortalBundle/Resources/style/helpcenter-style.scss';
        }

        return $this->assetDir.'/pub/src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-'.strtolower($direction).'-style.scss';
    }
}
