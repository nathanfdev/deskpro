<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\EmailTemplates;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 * @ApiDoc(target="all", section="Email Templates", output="Application\DeskPRO\Entity\Blob")
 * @Feature("email_templates")
 * @Rest\Route("/email_templates/email_assets")
 */
class AssetsController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Add a new asset",
     *     tags={"CRUD"="#ffa500"},
     *     requirements={
     *         {
     *             "name"="type",
     *             "description"="The asset type 'inline-image' or 'attachment'",
     *             "dataType"="string"
     *         }
     *     },
     *)
     * @Rest\Get("/{type}")
     *
     * @param string $type
     *
     * @return View
     */
    public function listAction($type = 'inline-image')
    {
        if (!in_array($type, ['inline-image', 'attachment'])) {
            throw $this->createNotFoundException('Invalid type');
        }
        $assets = $this->getManager()->getRepository(ThemeSetAsset::class)->findEmailAssetsEager($type);

        return View::create($this->wrap($assets));
    }

    /**
     * @ApiDoc(
     *     description="Add a new asset",
     *     tags={"CRUD"="#ffa500"},
     *     requirements={
     *         {
     *             "name"="type",
     *             "description"="The asset type 'inline-image' or 'attachment'",
     *             "dataType"="string"
     *         }
     *     },
     *     input={
     *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType"
     *     },
     *     output="Application\DeskPRO\Entity\Blob"
     *)
     * @Rest\Post("/{type}")
     *
     * @param Request $request
     * @param string  $type
     *
     * @return View
     */
    public function postAction(Request $request, $type = 'inline-image')
    {
        if (!in_array($type, ['inline-image', 'attachment'])) {
            throw $this->createNotFoundException('Invalid type');
        }
        $themeSet = $this->getManager()->getRepository(ThemeSet::class)->findOneBy([
            'theme_id' => 'email_templates',
        ]);
        $accept        = $this->getContainer()->getAttachmentAccepter();
        $entityManager = $this->getManager();

        $file = $request->files->get('file');

        $blob = $accept->accept($file);

        if ($type === 'inline-image' && strpos($blob->getContentType(), 'image') !== 0) {
            throw $this->createBadRequestException('Inline image must be images');
        }

        $asset = new ThemeSetAsset();

        $asset->setName($blob->getFilename());
        $asset->setThemeSet($themeSet);
        $asset->setTags([$type]);
        $asset->setBlob($blob);

        $entityManager->persist($asset);
        $entityManager->flush();

        return View::create($this->wrap($blob));
    }

    /**
     * Obviously it's an ability to erase what you've done.
     * Be careful there is no CTRL+Z shortcut.
     *
     * @ApiDoc(
     *      description="Delete an email asset",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the asset",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if everything is ok and there is no such resource anymore",
     *          404="Well, looks like either asset already deleted either it doesn't exists at all"
     *      }
     * )
     * @Rest\Delete("/{id}", requirements={"id"="\d+"})
     *
     * @param int $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        /** @var ThemeSetAsset $asset */
        $asset = $this->findOr404(ThemeSetAsset::class, $id);

        if (!in_array('inline-image', $asset->getTags()) && !in_array('attachment', $asset->getTags())) {
            throw $this->createNotFoundException('Invalid type');
        }

        $em = $this->getManager();
        $em->remove($asset);
        $em->flush();

        return View::create([], Response::HTTP_OK);
    }
}
