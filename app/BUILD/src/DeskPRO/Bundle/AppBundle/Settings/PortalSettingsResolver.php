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
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\DownloadsSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\FeedbackSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\GeneralSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\KbSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\NewsSettings;

/**
 * Class PortalSettingsResolver.
 */
class PortalSettingsResolver extends AbstractBrandAwareSettingsResolver implements AntiAbuseSettingsAwareInterface
{
    const SITE_NAME     = 'core.site_name';
    const SITE_URL      = 'core.site_url';
    const HELPDESK_NAME = 'core.deskpro_name';
    const HELPDESK_URL  = 'core.deskpro_url';

    const APPS_FEEDBACK  = 'core.apps_feedback';
    const APPS_KB        = 'core.apps_kb';
    const APPS_NEWS      = 'core.apps_news';
    const APPS_DOWNLOADS = 'core.apps_downloads';

    const IFACE_PORTAL = 'core.iface_portal';
    const IFACE_WIDGET = 'core.iface_widget';

    const SHOW_RATINGS           = 'user.show_ratings';
    const SHOW_RATINGS_MIN_VOTES = 'user.show_ratings_min_votes';
    const PUBLISH_COMMENTS       = 'user.publish_comments';

    const TAB_FEEDBACK  = 'user.portal_tab_feedback';
    const TAB_KB        = 'user.portal_tab_articles';
    const TAB_NEWS      = 'user.portal_tab_news';
    const TAB_DOWNLOADS = 'user.portal_tab_downloads';

    const SUBSCRIPTION_FEEDBACK  = 'user.feedback_subscriptions';
    const SUBSCRIPTION_KB        = 'user.kb_subscriptions';
    const SUBSCRIPTION_NEWS      = 'user.news_subscriptions';
    const SUBSCRIPTION_DOWNLOADS = 'user.downloads_subscriptions';

    /**
     * {@inheritdoc}
     *
     * @return PortalAntiAbuseSettings
     */
    public function getAntiAbuseSettings()
    {
        $model = new PortalAntiAbuseSettings();

        $accountRateLimit = $model->getAccountRateLimit();
        $agentRateLimit   = $model->getAgentRateLimit();
        $this->setRateLimitGroup($accountRateLimit->getRegistrationSettings(), 'rate_limit.registration');
        $this->setRateLimitGroup($accountRateLimit->getResetPasswordSettings(), 'rate_limit.reset_password');
        $this->setRateLimitGroup($agentRateLimit->getLoginSettings(), 'rate_limit.login.agent');

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
            ->setTime($this->getSetting($settingPrefix.'.time') / 60)
            ->setLockoutTime($this->getSetting($settingPrefix.'.lockout_time') / 60)
            ->setResponse($this->getSetting($settingPrefix.'.response'));
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

        $this->setRateLimitGroup($userRateLimit->getLoginSettings(), 'rate_limit.login'.$userType);
        $this->setRateLimitGroup($userRateLimit->getSubmitTicket(), 'rate_limit.submit_ticket'.$userType);
        $this->setRateLimitGroup($userRateLimit->getSubmitFeedback(), 'rate_limit.submit_feedback'.$userType);
        $this->setRateLimitGroup($userRateLimit->getSubmitComment(), 'rate_limit.submit_comment'.$userType);
        $this->setRateLimitGroup($userRateLimit->getUploadAttachment(), 'rate_limit.upload_attachment'.$userType);
        $this->setRateLimitGroup($userRateLimit->getShareContent(), 'rate_limit.share_content'.$userType);
    }

    /**
     * @return GeneralSettings
     */
    public function getGeneralSettings()
    {
        $model = new GeneralSettings();

        $model
            ->setSiteName($this->getSetting(self::SITE_NAME))
            ->setSiteUrl($this->getSetting(self::SITE_URL))
            ->setDeskproName($this->getSetting(self::HELPDESK_NAME))
            ->setDeskproUrl($this->getSetting(self::HELPDESK_URL))
            ->setAppsFeedback($this->getSetting(self::APPS_FEEDBACK))
            ->setAppsKb($this->getSetting(self::APPS_KB))
            ->setAppsNews($this->getSetting(self::APPS_NEWS))
            ->setAppsDownloads($this->getSetting(self::APPS_DOWNLOADS))
            ->setIfacePortal($this->getSetting(self::IFACE_PORTAL))
            ->setIfaceWidget($this->getSetting(self::IFACE_WIDGET))
            ->setShowRatings($this->getSetting(self::SHOW_RATINGS))
            ->setShowRatingsMinVotes($this->getSetting(self::SHOW_RATINGS_MIN_VOTES))
            ->setPublishComments($this->getSetting(self::PUBLISH_COMMENTS))
        ;

        return $model;
    }

    /**
     * @return KbSettings
     */
    public function getKbSettings()
    {
        $model = new KbSettings();

        $model
            ->setEnabled($this->getSetting(self::APPS_KB))
            ->setTabEnabled($this->getSetting(self::TAB_KB))
            ->setSubscriptions($this->getSetting(self::SUBSCRIPTION_KB))
        ;

        return $model;
    }

    /**
     * @return FeedbackSettings
     */
    public function getFeedbackSettings()
    {
        $model = new FeedbackSettings();

        $model
            ->setEnabled($this->getSetting(self::APPS_FEEDBACK))
            ->setTabEnabled($this->getSetting(self::TAB_FEEDBACK))
            ->setSubscriptions($this->getSetting(self::SUBSCRIPTION_FEEDBACK))
        ;

        return $model;
    }

    /**
     * @return NewsSettings
     */
    public function getNewsSettings()
    {
        $model = new NewsSettings();

        $model
            ->setEnabled($this->getSetting(self::APPS_NEWS))
            ->setTabEnabled($this->getSetting(self::TAB_NEWS))
            ->setSubscriptions($this->getSetting(self::SUBSCRIPTION_NEWS))
        ;

        return $model;
    }

    /**
     * @return DownloadsSettings
     */
    public function getDownloadsSettings()
    {
        $model = new DownloadsSettings();

        $model
            ->setEnabled($this->getSetting(self::APPS_DOWNLOADS))
            ->setTabEnabled($this->getSetting(self::TAB_DOWNLOADS))
            ->setSubscriptions($this->getSetting(self::SUBSCRIPTION_DOWNLOADS))
        ;

        return $model;
    }
}
