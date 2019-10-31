<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceNumberType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VoiceNumbersController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_numbers")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\VoiceNumber")
 * @ApiUserContext("admin", agent={"list", "get", "count"})
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceNumberType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\VoiceNumber"
 *      }
 *     }
 * )
 */
class VoiceNumbersController extends AbstractVoiceCrudController
{
    public static $entity       = VoiceNumber::class;
    public static $type         = VoiceNumberType::class;
    public static $listPaginate = false;

    /**
     * @Rest\Put("/{id}/disable", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function disableAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::DELETE, $this->getPermissionGroupEntityContext($id, $request));

        $entity = $this->findEntity($id, $request);
        if ($entity) {
            $em = $this->getManager();
            $em->remove($entity);
            $em->flush();
        }

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     *
     * @param VoiceNumber $entity
     */
    protected function deleteEntity($entity)
    {
        $account = $entity->getAccount();
        if ($account instanceof TwilioVoiceAccount) {
            $this->get('twilio_adapter')->releaseNumber($account, $entity->getSid());
        }

        parent::deleteEntity($entity);
    }
}
