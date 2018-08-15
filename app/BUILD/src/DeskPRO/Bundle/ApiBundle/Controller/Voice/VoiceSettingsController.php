<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceSettingsType;
use DeskPRO\Bundle\AppBundle\Settings\VoiceSettingsResolver;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VoiceSettingsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_settings")
 * @Feature("voice")
 * @ApiUserContext("admin", agent={"getSettings"})
 */
class VoiceSettingsController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Voice Channel",
     *     description="Global voice settings",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Settings\Model\VoiceSettings"
     * )
     *
     * @Rest\Get("")
     *
     * @return View
     */
    public function getSettingsAction()
    {
        return new View($this->wrap($this->get('voice_settings_resolver')->getVoiceSettings()));
    }

    /**
     * @ApiDoc(
     *     section="Voice Channel",
     *     description="Global voice settings",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     input="DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceSettingsType",
     *     noOutput=true
     * )
     *
     * @Rest\Put("")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function updateSettingsAction(Request $request)
    {
        $form = $this->createForm(VoiceSettingsType::class);
        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
        $settingsRepo = $this->getManager()->getRepository(Setting::class);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_AGENT_VOICEMAIL_TIMEOUT, $form->get('agent_voicemail_timeout')->getData());
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_GROUP_MISSED_CALL_TICKETS, $form->get('group_missed_call_tickets')->getData());
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_GROUP_MISSED_CALL_TICKETS_TIMEOUT, $form->get('group_missed_call_tickets_timeout')->getData());

        $this->getManager()->getConnection()->executeUpdate(
            'UPDATE voice_accounts SET date_sync = :date_sync',
            [
                'date_sync' => date('c'),
            ]
        );

        return new View(null, Response::HTTP_NO_CONTENT);
    }
}
