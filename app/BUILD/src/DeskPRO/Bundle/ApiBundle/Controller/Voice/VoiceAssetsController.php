<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceAssetType;
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
     *      "class"="DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceAssetType",
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
