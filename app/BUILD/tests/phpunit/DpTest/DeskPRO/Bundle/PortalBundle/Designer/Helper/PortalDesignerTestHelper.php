<?php

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
