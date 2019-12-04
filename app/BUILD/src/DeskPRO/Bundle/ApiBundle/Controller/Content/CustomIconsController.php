<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CustomIconsController.
 *
 * @ApiModes("all")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\Blob")
 * @Rest\Route("/custom_icons")
 * @ApiUserContext("agent")
 */
class CustomIconsController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Content",
     *     description="Add a new custom icon",
     *     input={
     *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType"
     *     },
     *     output="Application\DeskPRO\Entity\Blob"
     * )
     * @Rest\Post("/upload")
     *
     * @param Request $request
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return View
     */
    public function uploadAction(Request $request)
    {
        $themeSet      = $this->get('dp.portal.designer.brand_theme_manager')->getCurrentThemeSet();
        $accept        = $this->getContainer()->getAttachmentAccepter();
        $entityManager = $this->getManager();

        $file = $request->files->get('file');

        $blob = $accept->accept($file);

        if (strpos($blob->getContentType(), 'image') !== 0) {
            throw $this->createBadRequestException('Icons must be images');
        }

        $asset = new ThemeSetAsset();

        $name     = $request->get('name');
        $keywords = $request->get('keywords');

        if ($name) {
            $asset->setName($blob->getFilename());
        } else {
            $asset->setName($name);
        }
        $tags = ['custom_icon'];
        if ($keywords) {
            $tags = array_merge($tags, explode(' ', $keywords));
        }
        $asset->setThemeSet($themeSet);
        $asset->setTags($tags);
        $asset->setBlob($blob);

        $entityManager->persist($asset);
        $entityManager->flush();

        return View::create($this->wrap($blob));
    }

    /**
     * @ApiDoc(
     *     section="Content",
     *     description="List custom icons of the current theme",
     *     input={
     *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType"
     *     },
     *     output="Application\DeskPRO\Entity\Blob"
     * )
     * @Rest\Get("")
     *
     * @return View
     */
    public function listAction()
    {
        $em    = $this->getManager();
        $icons = $em->getRepository(ThemeSetAsset::class)->getCustomIcons();

        return View::create($this->wrap($icons));
    }
}
