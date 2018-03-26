<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\AbstractRateLimitGroup;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalAntiAbuseSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalUserRateLimit;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitLockoutGroup;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\DownloadsSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\FeedbackSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\GeneralSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\GuidesSettings;
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
    const APPS_GUIDES    = 'core.apps_guides';

    const IFACE_PORTAL = 'core.iface_portal';
    const IFACE_WIDGET = 'core.iface_widget';

    const SHOW_RATINGS           = 'user.show_ratings';
    const SHOW_RATINGS_MIN_VOTES = 'user.show_ratings_min_votes';
    const PUBLISH_COMMENTS       = 'user.publish_comments';

    const TAB_FEEDBACK  = 'user.portal_tab_feedback';
    const TAB_KB        = 'user.portal_tab_articles';
    const TAB_NEWS      = 'user.portal_tab_news';
    const TAB_DOWNLOADS = 'user.portal_tab_downloads';
    const TAB_GUIDES    = 'user.portal_tab_guides';

    const SUBSCRIPTION_FEEDBACK  = 'user.feedback_subscriptions';
    const SUBSCRIPTION_KB        = 'user.kb_subscriptions';
    const SUBSCRIPTION_NEWS      = 'user.news_subscriptions';
    const SUBSCRIPTION_DOWNLOADS = 'user.downloads_subscriptions';
    const SUBSCRIPTION_GUIDES    = 'user.downloads_guides';

    const KB_WITH_TREE = 'user.kb_categories_with_tree';

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
        $this->setRateLimitLockoutGroup($accountRateLimit->getRegistrationSettings(), 'rate_limit.registration');
        $this->setRateLimitLockoutGroup($accountRateLimit->getResetPasswordSettings(), 'rate_limit.reset_password');
        $this->setRateLimitOptionsGroup($agentRateLimit->getLoginSettings(), 'rate_limit.login.agent');

        $this->setUserRateLimit($model->getUserRateLimit());
        $this->setUserRateLimit($model->getGuestRateLimit(), 'guest');

        return $model;
    }

    /**
     * @param AbstractRateLimitGroup $group
     * @param string                 $settingPrefix
     */
    private function setRateLimitBaseGroup(AbstractRateLimitGroup $group, $settingPrefix)
    {
        $group
            ->setEnabled($this->getSetting($settingPrefix.'.enabled'))
            ->setLimit($this->getSetting($settingPrefix.'.limit'))
            ->setTime($this->getSetting($settingPrefix.'.time') / 60)
        ;
    }

    /**
     * @param RateLimitLockoutGroup $group
     * @param string                $settingPrefix
     */
    private function setRateLimitLockoutGroup(RateLimitLockoutGroup $group, $settingPrefix)
    {
        $this->setRateLimitBaseGroup($group, $settingPrefix);
        $group->setLockoutTime($this->getSetting($settingPrefix.'.lockout_time') / 60);
    }

    /**
     * @param RateLimitOptionsGroup $group
     * @param string                $settingPrefix
     */
    private function setRateLimitOptionsGroup(RateLimitOptionsGroup $group, $settingPrefix)
    {
        $this->setRateLimitBaseGroup($group, $settingPrefix);
        $group
            ->setLockoutTime($this->getSetting($settingPrefix.'.lockout_time') / 60)
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

        $this->setRateLimitOptionsGroup($userRateLimit->getLoginSettings(), 'rate_limit.login'.$userType);
        $this->setRateLimitOptionsGroup($userRateLimit->getSubmitTicket(), 'rate_limit.submit_ticket'.$userType);
        $this->setRateLimitOptionsGroup($userRateLimit->getSubmitFeedback(), 'rate_limit.submit_feedback'.$userType);
        $this->setRateLimitOptionsGroup($userRateLimit->getSubmitComment(), 'rate_limit.submit_comment'.$userType);
        $this->setRateLimitOptionsGroup($userRateLimit->getUploadAttachment(), 'rate_limit.upload_attachment'.$userType);
        $this->setRateLimitOptionsGroup($userRateLimit->getShareContent(), 'rate_limit.share_content'.$userType);
    }

    /**
     * @param Brand $brand
     *
     * @return GeneralSettings
     */
    public function getGeneralSettings(Brand $brand = null)
    {
        $brand = $brand ?: $this->settingsResolver->getActiveBrand();
        $model = new GeneralSettings();
        $model
            ->setSiteName($this->getSetting(self::SITE_NAME, $brand))
            ->setSiteUrl($this->getSetting(self::SITE_URL, $brand))
            ->setDeskproName($this->getSetting(self::HELPDESK_NAME, $brand))
            ->setDeskproUrl($this->getSetting(self::HELPDESK_URL, $brand))
            ->setAppsFeedback($this->getSetting(self::APPS_FEEDBACK, $brand))
            ->setAppsKb($this->getSetting(self::APPS_KB, $brand))
            ->setAppsNews($this->getSetting(self::APPS_NEWS, $brand))
            ->setAppsDownloads($this->getSetting(self::APPS_DOWNLOADS, $brand))
            ->setAppsGuides($this->getSetting(self::APPS_GUIDES, $brand))
            ->setIfacePortal($this->getSetting(self::IFACE_PORTAL, $brand))
            ->setIfaceWidget($this->getSetting(self::IFACE_WIDGET, $brand))
            ->setShowRatings($this->getSetting(self::SHOW_RATINGS, $brand))
            ->setShowRatingsMinVotes($this->getSetting(self::SHOW_RATINGS_MIN_VOTES, $brand))
            ->setPublishComments($this->getSetting(self::PUBLISH_COMMENTS, $brand))
            ->setBrand($brand)
        ;

        return $model;
    }

    /**
     * @param Brand $brand
     *
     * @return KbSettings
     */
    public function getKbSettings(Brand $brand = null)
    {
        $brand = $brand ?: $this->settingsResolver->getActiveBrand();
        $model = new KbSettings();
        $model
            ->setEnabled($this->getSetting(self::APPS_KB, $brand))
            ->setTabEnabled($this->getSetting(self::TAB_KB, $brand))
            ->setSubscriptions($this->getSetting(self::SUBSCRIPTION_KB, $brand))
            ->setKnowledgebaseDeepTree($this->getSetting(self::KB_WITH_TREE, $brand))
            ->setBrand($brand)
        ;

        return $model;
    }

    /**
     * @param Brand $brand
     *
     * @return FeedbackSettings
     */
    public function getFeedbackSettings(Brand $brand = null)
    {
        $brand = $brand ?: $this->settingsResolver->getActiveBrand();
        $model = new FeedbackSettings();
        $model
            ->setEnabled($this->getSetting(self::APPS_FEEDBACK, $brand))
            ->setTabEnabled($this->getSetting(self::TAB_FEEDBACK, $brand))
            ->setSubscriptions($this->getSetting(self::SUBSCRIPTION_FEEDBACK, $brand))
            ->setBrand($brand)
        ;

        return $model;
    }

    /**
     * @param Brand $brand
     *
     * @return NewsSettings
     */
    public function getNewsSettings(Brand $brand = null)
    {
        $brand = $brand ?: $this->settingsResolver->getActiveBrand();
        $model = new NewsSettings();
        $model
            ->setEnabled($this->getSetting(self::APPS_NEWS, $brand))
            ->setTabEnabled($this->getSetting(self::TAB_NEWS, $brand))
            ->setSubscriptions($this->getSetting(self::SUBSCRIPTION_NEWS, $brand))
            ->setBrand($brand)
    ;

        return $model;
    }

    /**
     * @param Brand $brand
     *
     * @return DownloadsSettings
     */
    public function getDownloadsSettings(Brand $brand = null)
    {
        $brand = $brand ?: $this->settingsResolver->getActiveBrand();
        $model = new DownloadsSettings();
        $model
            ->setEnabled($this->getSetting(self::APPS_DOWNLOADS, $brand))
            ->setTabEnabled($this->getSetting(self::TAB_DOWNLOADS, $brand))
            ->setSubscriptions($this->getSetting(self::SUBSCRIPTION_DOWNLOADS, $brand))
            ->setBrand($brand)
        ;

        return $model;
    }

    /**
     * @param Brand $brand
     *
     * @return GuidesSettings
     */
    public function getGuidesSettings(Brand $brand = null)
    {
        $brand = $brand ?: $this->settingsResolver->getActiveBrand();
        $model = new GuidesSettings();
        $model
            ->setEnabled($this->getSetting(self::APPS_GUIDES, $brand))
            ->setTabEnabled($this->getSetting(self::TAB_GUIDES, $brand))
            ->setSubscriptions($this->getSetting(self::SUBSCRIPTION_GUIDES, $brand))
            ->setBrand($brand)
        ;

        return $model;
    }
}
