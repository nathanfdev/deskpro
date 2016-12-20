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

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 * @Feature("new_email_templates")
 * @Rest\Route("/email_templates/email_assets")
 */
class AssetsController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Email Templates",
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
     *     section="Email Templates",
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
}
