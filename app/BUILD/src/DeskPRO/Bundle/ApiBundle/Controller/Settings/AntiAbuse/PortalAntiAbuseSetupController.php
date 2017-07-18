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

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings\AntiAbuse;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\Portal\PortalAntiAbuseSettingsType;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\AbstractRateLimitGroup;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalAntiAbuseSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalUserRateLimit;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitLockoutGroup;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class PortalAntiAbuseSetupController.
 *
 * @ApiModes("all")
 * @Rest\Route("/settings/anti_abuse/portal")
 * @ApiDoc(target="all", output="DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalAntiAbuseSettings")
 * @ApiDoc(
 *     target="putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\Portal\PortalAntiAbuseSettingsType"
 *     }
 * )
 */
class PortalAntiAbuseSetupController extends AbstractAntiAbuseSetupController
{
    protected static $resolver = 'portal_settings_resolver';
    protected static $model    = PortalAntiAbuseSettingsType::class;

    /**
     * {@inheritdoc}
     *
     * @param PortalAntiAbuseSettings $model
     */
    protected function persistModel($model)
    {
        $accountRateLimit = $model->getAccountRateLimit();
        $agentRateLimit   = $model->getAgentRateLimit();
        $this->updateRateLimitOptionGroup($agentRateLimit->getLoginSettings(), 'rate_limit.login.agent');
        $this->updateRateLimitLockoutGroup($accountRateLimit->getRegistrationSettings(), 'rate_limit.registration');
        $this->updateRateLimitLockoutGroup($accountRateLimit->getResetPasswordSettings(), 'rate_limit.reset_password');

        $this->setUserRateLimit($model->getUserRateLimit());
        $this->setUserRateLimit($model->getGuestRateLimit(), 'guest');
    }

    /**
     * @param RateLimitLockoutGroup $group
     * @param string                $settingPrefix
     */
    private function updateRateLimitLockoutGroup(RateLimitLockoutGroup $group, $settingPrefix)
    {
        $this->updateBaseRateLimitGroup($group, $settingPrefix);

        $repository = $this->getSettingRepository();
        $repository->updateSetting($settingPrefix.'.lockout_time', $group->getLockoutTime() * 60);
    }

    /**
     * @param RateLimitOptionsGroup $group
     * @param string                $settingPrefix
     */
    private function updateRateLimitOptionGroup(RateLimitOptionsGroup $group, $settingPrefix)
    {
        $this->updateBaseRateLimitGroup($group, $settingPrefix);

        $repository = $this->getSettingRepository();
        $repository
            ->updateSetting($settingPrefix.'.lockout_time', $group->getLockoutTime() * 60)
            ->updateSetting($settingPrefix.'.response', $group->getResponse())
        ;
    }

    /**
     * @param AbstractRateLimitGroup $group
     * @param string                 $settingPrefix
     */
    private function updateBaseRateLimitGroup(AbstractRateLimitGroup $group, $settingPrefix)
    {
        $repository = $this->getSettingRepository();
        $repository
            ->updateSetting($settingPrefix.'.enabled', $group->isEnabled())
            ->updateSetting($settingPrefix.'.limit', $group->getLimit())
            ->updateSetting($settingPrefix.'.time', $group->getTime() * 60)
        ;
    }

    /**
     * @param PortalUserRateLimit $userRateLimit
     * @param string              $userType
     */
    private function setUserRateLimit(PortalUserRateLimit $userRateLimit, $userType = '')
    {
        if ($userType) {
            $userType = '.'.$userType;
        }

        $this->updateRateLimitOptionGroup($userRateLimit->getLoginSettings(), 'rate_limit.login'.$userType);
        $this->updateRateLimitOptionGroup($userRateLimit->getSubmitTicket(), 'rate_limit.submit_ticket'.$userType);
        $this->updateRateLimitOptionGroup($userRateLimit->getSubmitFeedback(), 'rate_limit.submit_feedback'.$userType);
        $this->updateRateLimitOptionGroup($userRateLimit->getSubmitComment(), 'rate_limit.submit_comment'.$userType);
        $this->updateRateLimitOptionGroup($userRateLimit->getUploadAttachment(), 'rate_limit.upload_attachment'.$userType);
        $this->updateRateLimitOptionGroup($userRateLimit->getShareContent(), 'rate_limit.share_content'.$userType);
    }
}
