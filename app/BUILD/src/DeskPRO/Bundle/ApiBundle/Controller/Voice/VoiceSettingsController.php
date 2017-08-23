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
     * @return View
     */
    public function updateSettingsAction(Request $request)
    {
        $form = $this->createForm(VoiceSettingsType::class);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
        $settingsRepo = $this->getManager()->getRepository(Setting::class);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_AGENT_VOICEMAIL_TIMEOUT, $form->get('agent_voicemail_timeout')->getData());

        $this->getManager()->getConnection()->executeUpdate(
            'UPDATE voice_accounts SET date_sync = :date_sync',
            [
                'date_sync' => date('c'),
            ]
        );

        return new View(null, Response::HTTP_NO_CONTENT);
    }
}
