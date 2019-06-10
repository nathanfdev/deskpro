<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceAssetType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class VoiceAssetsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_assets")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class VoiceAssetsController extends CrudController
{
    public static $entity = AbstractVoiceAsset::class;
    public static $type   = VoiceAssetType::class;

    /**
     * {@inheritdoc}
     */
    protected function instantiateEntity(Request $request)
    {
        // it's abstract class, can't instantiate

        return;
    }
}
