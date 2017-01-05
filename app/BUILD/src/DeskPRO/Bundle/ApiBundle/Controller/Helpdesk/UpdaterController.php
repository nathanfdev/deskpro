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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Helpdesk;

use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\EntityRepository\Setting as SettingRepository;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\UpdaterSettingsType;
use DeskPRO\Bundle\AppBundle\Settings\UpdaterSettingsResolver;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ApiModes("all")
 * @ApiUserContext("admin")
 */
class UpdaterController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Helpdesk",
     *     description="Get the updater settings",
     *     statusCodes={
     *         200="Success"
     *     }
     * )
     * @ApiUnstable()
     * @Rest\Get("/helpdesk/updater/settings")
     */
    public function updaterSettingsAction()
    {
        return View::create(
            ['data' => $this->get('updater_settings_resolver')->getUpdaterSettings()],
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Set updater settings",
     *      statusCodes={
     *          204="Returned in case of successful resource modify",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Put("/helpdesk/updater/settings")
     * @ApiUnstable()
     *
     * @param Request $request
     *
     * @return View
     */
    public function putAction(Request $request)
    {
        if ($this->get('deskpro.app_env')->getConfig('settings.disable_admin_deskpro_updates')) {
            throw $this->createAccessDeniedException('disable_admin_deskpro_updates is enabled');
        }

        $model         = $this->get('updater_settings_resolver')->getUpdaterSettings();
        $type          = UpdaterSettingsType::class;
        $isModify      = true;
        $status        = Response::HTTP_NO_CONTENT;
        $partialUpdate = true;
        $options       = [];

        $form    = $this->createForm($type, $model, $options);
        $decoded = json_decode($request->getContent(), true);

        $form->submit($decoded, !$partialUpdate);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        /** @var SettingRepository $settingRepos */
        $settingRepos = $this->getRepository(Setting::class);
        $settingRepos
            ->updateSetting(UpdaterSettingsResolver::AUTO_UPDATER_ENABLED,        $model->isEnabled() ? 1 : 0)
            ->updateSetting(UpdaterSettingsResolver::AUTO_UPDATER_TIME_OF_DAY,    $model->getTimeOfDay())
            ->updateSetting(UpdaterSettingsResolver::AUTO_UPDATER_TIMEZONE,       $model->getTimezone()->getName())
            ->updateSetting(UpdaterSettingsResolver::AUTO_UPDATER_INTERVAL,       $model->getIntervalDays())
            ->updateSetting(UpdaterSettingsResolver::AUTO_UPDATER_NEXT_TIME,      $model->calculateNextTimeUtc() ? $model->calculateNextTimeUtc()->format('Y-m-d H:i:s') : null)
            ->updateSetting(UpdaterSettingsResolver::AUTO_UPDATER_NEXT_IS_MANUAL, 0)
        ;

        $view = View::create(!$isModify ? $this->wrap($model) : null, $status);

        $route = preg_replace('/_post$/', '_get', $request->get('_route'));
        $view->setLocation($this->generateUrl($route));

        return $view;
    }

    /**
     * @ApiDoc(
     *      description="Manually schedule an upgrade to run right now",
     *      statusCodes={
     *          204="Returned in case of successful resource modify",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Post("/helpdesk/updater/manual-schedule")
     * @ApiUnstable()
     *
     * @param Request $request
     *
     * @return View
     */
    public function manualSheduleAction(Request $request)
    {
        if ($this->get('deskpro.app_env')->getConfig('settings.disable_admin_deskpro_updates')) {
            throw $this->createAccessDeniedException('disable_admin_deskpro_updates is enabled');
        }

        $secs = (int) $request->request->get('delay', 60);
        if ($secs < 0) {
            $secs = 60;
        }

        $setDate = new \DateTime();
        $setDate->modify('+'.$secs.' seconds');

        /** @var SettingRepository $settingRepos */
        $settingRepos = $this->getRepository(Setting::class);
        $settingRepos
            ->updateSetting(UpdaterSettingsResolver::AUTO_UPDATER_NEXT_TIME,      $setDate->format('Y-m-d H:i:s'))
            ->updateSetting(UpdaterSettingsResolver::AUTO_UPDATER_NEXT_IS_MANUAL, 1)
        ;

        return View::create($this->wrap(['success' => true]));
    }

    /**
     * @ApiDoc(
     *     section="Helpdesk",
     *     description="Get the updater status",
     *     statusCodes={
     *         200="Success"
     *     }
     * )
     * @ApiUnstable()
     * @Rest\Get("/helpdesk/updater/status")
     */
    public function updaterStatusAction()
    {
        return View::create(
            ['data' => $this->get('updater_settings_resolver')->getUpdaterStatus()],
            Response::HTTP_OK
        );
    }
}
