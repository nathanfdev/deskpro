<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;

/**
 * Class TemplatesProcessor.
 */
class TemplatesProcessor extends Base
{
    const JOB_TYPE = 'reset.templates';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM templates');
        $this->connection->executeUpdate('DELETE FROM theme_set_assets');
        $this->connection->executeUpdate('DELETE FROM brands');
        $this->connection->executeUpdate('DELETE FROM theme_sets');

        // Reset themes
        $standardTheme = new ThemeSet();
        $standardTheme->setThemeId('standard');
        $this->em->persist($standardTheme);

        $sidebarTheme = new ThemeSet();
        $sidebarTheme->setThemeId('sidebar');
        $this->em->persist($sidebarTheme);

        // Reset brand
        $brand = new Brand();
        $brand->setName('Default');
        $brand->setThemeSet($standardTheme);
        $this->em->persist($brand);
        $this->em->flush();

        // Reset default brand
        $setting = $this->em->getRepository(Setting::class)->findOneBy(['name' => 'portal.default_brand']);
        if (!$setting) {
            $setting = new Setting();
        }

        $setting->name  = 'portal.default_brand';
        $setting->value = $brand->getId();
        $this->em->persist($brand);
        $this->em->flush();
    }
}
