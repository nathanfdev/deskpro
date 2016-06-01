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

namespace DeskPRO\Bundle\AppBundle\Settings;

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalAntiAbuseSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalUserRateLimit;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitGroup;

/**
 * Class PortalSettingsResolver.
 */
class PortalSettingsResolver extends AbstractBrandAwareSettingsResolver implements AntiAbuseSettingsAwareInterface
{
    /**
     * {@inheritdoc}
     *
     * @return PortalAntiAbuseSettings
     */
    public function getAntiAbuseSettings()
    {
        $model = new PortalAntiAbuseSettings();

        $accountRateLimit = $model->getAccountRateLimit();
        $this->setRateLimitGroup($accountRateLimit->getLoginSettings(), 'rate_limit.login');
        $this->setRateLimitGroup($accountRateLimit->getRegistrationSettings(), 'rate_limit.registration');
        $this->setRateLimitGroup($accountRateLimit->getResetPasswordSettings(), 'rate_limit.reset_password');

        $this->setUserRateLimit($model->getUserRateLimit());
        $this->setUserRateLimit($model->getGuestRateLimit(), 'guest');

        return $model;
    }

    /**
     * @param RateLimitGroup $group
     * @param string         $settingPrefix
     */
    private function setRateLimitGroup(RateLimitGroup $group, $settingPrefix)
    {
        $group
            ->setEnabled($this->getSetting($settingPrefix.'.enabled'))
            ->setLimit($this->getSetting($settingPrefix.'.limit'))
            ->setTime($this->getSetting($settingPrefix.'.time'))
            ->setLockoutTime($this->getSetting($settingPrefix.'.lockout_time'))
            ->setResponse($this->getSetting($settingPrefix.'.response'))
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

        $this->setRateLimitGroup($userRateLimit->getSubmitTicket(), 'rate_limit.submit_ticket'.$userType);
        $this->setRateLimitGroup($userRateLimit->getSubmitFeedback(), 'rate_limit.submit_feedback'.$userType);
        $this->setRateLimitGroup($userRateLimit->getSubmitComment(), 'rate_limit.submit_comment'.$userType);
        $this->setRateLimitGroup($userRateLimit->getUploadAttachment(), 'rate_limit.upload_attachment'.$userType);
        $this->setRateLimitGroup($userRateLimit->getShareContent(), 'rate_limit.share_content'.$userType);
    }
}
