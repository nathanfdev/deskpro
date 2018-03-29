<?php

namespace DpBehat\Portal;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DpBehat\Data\DataContext;

class PortalBrandThemeContext extends BasePortalContext
{
    /**
     * @Given the default brand is using the :theme_id theme
     *
     * @param string $themeId
     */
    public function theActiveBrandHasTheme($themeId)
    {
        $themeSet = $this->em()->getRepository(ThemeSet::class)->findOneBy(['theme_id' => $themeId]);

        // portal fixtures BC
        $brand = $this->getEm()->getRepository(Brand::class)->findOneBy(['theme_set' => $themeSet]);
        if ($brand) {
            return;
        }

        $brand = DataContext::getReference('defaultBrand');
        $brand->setThemeSet($themeSet);

        $this->em()->persist($brand);
        $this->em()->flush();
    }
}
