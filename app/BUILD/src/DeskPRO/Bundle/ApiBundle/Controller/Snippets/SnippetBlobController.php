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

namespace DeskPRO\Bundle\ApiBundle\Controller\Snippets;

use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 * @ApiDoc(target="all", section="Snippets", output="Application\DeskPRO\Entity\Blob")
 * @Feature("new_snippets")
 * @Rest\Route("/snippets/attachment")
 */
class SnippetBlobController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Add a new attachment",
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
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $accept        = $this->getContainer()->getAttachmentAccepter();
        $entityManager = $this->getManager();

        $file = $request->files->get('file');

        $blob = $accept->accept($file);

        $entityManager->persist($blob);
        $entityManager->flush();

        return View::create($this->wrap($blob));
    }

    /**
     * Obviously it's an ability to erase what you've done.
     * Be careful there is no CTRL+Z shortcut.
     *
     * @ApiDoc(
     *      description="Delete an attachment",
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
        $asset = $this->findOr404(Blob::class, $id);

        $em = $this->getManager();
        $em->remove($asset);
        $em->flush();

        return View::create([], Response::HTTP_OK);
    }
}
