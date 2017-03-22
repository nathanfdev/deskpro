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

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceAssetType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VoiceAssetsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_assets")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class VoiceAssetsController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Create a new voice asset",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset",
     *     input={
     *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceAssetType",
     *      "options"={
     *          "data"="DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\VoiceUploadAsset"
     *      }
     *     }
     * )
     *
     * @Rest\Post("/create")
     *
     * @param Request $request
     *
     * @return View
     */
    public function createAssetAction(Request $request)
    {
        $form = $this->createForm(VoiceAssetType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        /** @var AbstractVoiceAsset $entity */
        $entity = $form->getData();

        $em = $this->getManager();
        $em->persist($entity);
        $em->flush();

        return new View($this->wrap($entity), Response::HTTP_CREATED);
    }
}
