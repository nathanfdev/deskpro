<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AuditBundle\Entity\NamingStrategy;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\AuditBundle\Log\AuditLog;
use Doctrine\ORM\EntityManager;

/**
 * Class ThemeNamingStrategy.
 */
class ThemeNamingStrategy implements NamingStrategyInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * ThemeNamingStrategy constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param          $object
     * @param AuditLog $log
     *
     * @return string
     */
    public function getName($object, AuditLog $log)
    {
        $name = '';
        switch (true) {
            case $object instanceof ThemeSetAsset:
                $tags = $object->getTags();
                $name .= $object->getThemeSet() ? $this->getBrandThemeString($object->getThemeSet()) : '';
                $name .= $tags ? ' ('.array_pop($tags).')' : '';
                break;
            case $object instanceof Template:
                $name .= $object->getThemeSet() ? $this->getBrandThemeString($object->getThemeSet()) : '';
                $name .= " ({$object->getName()})";
                break;
            case $object instanceof ThemeSet:
                $name = $this->getBrandThemeString($object);
        }

        return $name;
    }

    /**
     * @param ThemeSet $themeSet
     * @param bool     $editTheme
     *
     * @return Brand
     */
    private function getBrand(ThemeSet $themeSet, $editTheme = false)
    {
        $key             = $editTheme ? 'theme_set' : 'edit_theme_set';
        $brandRepository = $this->em->getRepository(Brand::class);

        return $brandRepository->findOneBy([$key => $themeSet]);
    }

    /**
     * @param ThemeSet $themeSet
     *
     * @return string
     */
    private function getBrandThemeString(ThemeSet $themeSet)
    {
        $name = '';
        if ($brand = $this->getBrand($themeSet)) {
            $name = $brand->getName().' (Brand) - '.$themeSet->getThemeId();
        } elseif ($brand = $this->getBrand($themeSet, true)) {
            $name = $brand->getName().' (Brand) - Preview '.$themeSet->getThemeId();
        }

        return $name;
    }
}
