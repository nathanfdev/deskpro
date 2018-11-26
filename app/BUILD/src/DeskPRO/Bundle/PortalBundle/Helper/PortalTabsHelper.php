<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Helper;

use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\UseSectionVoter;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Orb\Util\Arrays;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class PortalTabsHelper
{
    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var AuthorizationChecker
     */
    private $auth_checker;

    public function __construct(BrandStack $brand_stack, AuthorizationChecker $auth_checker)
    {
        $this->brand_stack  = $brand_stack;
        $this->auth_checker = $auth_checker;
    }

    /**
     * @return array the names of the tabs that are to be used in this request, and in the correct order
     */
    public function getTabsDisplay()
    {
        $tabs = [];

        $order = $this->findTabOrder();

        foreach ($order as $tab_type) {
            switch ($tab_type) {

                case 'news':
                    if ($this->getBrandSetting('user.portal_tab_news')
                        && $this->auth_checker->isGranted(UseSectionVoter::USE_NEWS)) {
                        $tabs[] = $tab_type;
                    }
                    break;
                case 'articles':
                    if ($this->getBrandSetting('user.portal_tab_articles')
                        && $this->auth_checker->isGranted(UseSectionVoter::USE_ARTICLES)) {
                        $tabs[] = $tab_type;
                    }
                    break;
                case 'feedback':
                    if ($this->getBrandSetting('user.portal_tab_feedback')
                        && $this->auth_checker->isGranted(UseSectionVoter::USE_FEEDBACK)) {
                        $tabs[] = $tab_type;
                    }
                    break;
                case 'downloads':
                    if ($this->getBrandSetting('user.portal_tab_downloads')
                        && $this->auth_checker->isGranted(UseSectionVoter::USE_DOWNLOADS)) {
                        $tabs[] = $tab_type;
                    }
                    break;
                case 'guides':
                    if ($this->getBrandSetting('user.portal_tab_guides')
                        && $this->auth_checker->isGranted(UseSectionVoter::USE_GUIDES)) {
                        $tabs[] = $tab_type;
                    }
                    break;
                case 'newticket':
                    if ($this->getBrandSetting('user.portal_tab_tickets')
                        && $this->auth_checker->isGranted(UseSectionVoter::VIEW_TICKETS_LINK)) {
                        $tabs[] = $tab_type;
                    }
                    break;
            }
        }

        return $tabs;
    }

    /**
     * @return array|mixed
     */
    protected function findTabOrder()
    {
        $order = $this->getBrandSetting('user.portal_tabs_order');

        if ($order) {
            $order = explode(',', $order);
            $order = Arrays::removeFalsey($order);
        } else {
            $order = [];
        }

        $order = array_merge($order, [
            'articles',
            'guides',
            'news',
            'feedback',
            'downloads',
            'newticket',
        ]);

        $order = array_unique($order);

        return $order;
    }

    protected function getBrandSetting($setting_name)
    {
        return $this->brand_stack->getActive()->getSetting($setting_name);
    }
}
