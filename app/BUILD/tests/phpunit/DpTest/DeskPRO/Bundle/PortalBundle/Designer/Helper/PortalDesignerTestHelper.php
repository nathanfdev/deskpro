<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\PortalBundle\Designer\Helper;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\BlobStorage;
use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class PortalDesignerTestHelper.
 */
trait PortalDesignerTestHelper
{
    /**
     * @return EntityManager
     */
    abstract protected function getEntityManager();

    /**
     * @param string $class
     *
     * @return EntityRepository
     */
    abstract protected function getRepository($class);

    // Find helpers ----------------------------------------------------------------------------------------------------

    /**
     * @param ThemeSet $theme_set
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset[]
     */
    protected function findAssets(ThemeSet $theme_set)
    {
        return $this->getRepository(ThemeSetAsset::class)->findBy(['theme_set' => $theme_set]);
    }

    /**
     * @param ThemeSet $theme_set
     *
     * @return Template[]
     */
    protected function findTemplates(ThemeSet $theme_set)
    {
        return $this->getRepository(Template::class)->findBy(['theme_set' => $theme_set]);
    }

    // Clean helpers ---------------------------------------------------------------------------------------------------

    /**
     * @param ThemeSet $theme_set
     */
    protected function cleanThemeSetAssets(ThemeSet $theme_set)
    {
        $assets = $this->findAssets($theme_set);
        foreach ($assets as $asset) {
            $this->getEntityManager()->remove($asset);
        }
        $this->getEntityManager()->flush();
    }

    // Mock helpers ----------------------------------------------------------------------------------------------------

    /**
     * @param ThemeSet    $theme_set
     * @param bool        $blob
     * @param null|string $name
     * @param array       $tags
     *
     * @return ThemeSetAsset
     */
    protected function persistDummyThemeSetAsset(ThemeSet $theme_set, $blob = true, $name = null, $tags = ['test'])
    {
        $name or $name = uniqid();
        $asset         = new ThemeSetAsset();
        $asset->setThemeSet($theme_set);
        $asset->setName($name);
        $asset->setTags($tags);
        if ($blob) {
            $asset->setBlob($this->persistDummyBlob());
        }

        $this->getEntityManager()->persist($asset);
        $this->getEntityManager()->flush();

        return $asset;
    }

    /**
     * @param bool $file
     *
     * @return Blob
     */
    protected function persistDummyBlob($file = true)
    {
        $em = $this->getEntityManager();

        $blob = new Blob();
        $blob->setFilename(uniqid());
        $blob->blob_hash    = md5('test');
        $blob->content_type = 'text/css';
        $em->persist($blob);
        $em->flush();

        if ($file) {
            $storage          = new BlobStorage();
            $storage->blob_id = $blob->getId();
            $storage->data    = uniqid();
            $blob->blob_hash  = md5($storage->data);
            $em->persist($blob);
            $em->persist($storage);
            $em->flush();
        }

        return $blob;
    }

    /**
     * @param ThemeSet $theme_set
     * @param int      $num
     */
    protected function persistDummyTemplates(ThemeSet $theme_set, $num)
    {
        for ($i = 1; $i <= $num; ++$i) {
            $tpl                = new Template();
            $tpl->name          = "tpl_{$i}";
            $tpl->theme_set     = $theme_set;
            $tpl->template_code = $tpl->template_compiled = "tpl_{$i}_code";
            $this->getEntityManager()->persist($tpl);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * @param ThemeSet $theme_set
     * @param int      $num
     * @param array    $tags
     */
    protected function persistDummyThemeSetAssets(ThemeSet $theme_set, $num, $tags = ['test'])
    {
        for ($i = 0; $i < $num; ++$i) {
            $this->persistDummyThemeSetAsset($theme_set, true, null, $tags);
        }
    }
}
