<?php

namespace DeskPRO\Bundle\PortalBundle\View;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\DataService\ArticlesDataService;
use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatDataService;
use DeskPRO\Bundle\AppBundle\DataService\Community\CommunityDataService;
use DeskPRO\Bundle\AppBundle\DataService\DirectMessageThreadDataService;
use DeskPRO\Bundle\AppBundle\DataService\DownloadsDataService;
use DeskPRO\Bundle\AppBundle\DataService\GuidesDataService;
use DeskPRO\Bundle\AppBundle\DataService\NewsDataService;
use DeskPRO\Bundle\AppBundle\DataService\TicketsDataService;
use DeskPRO\Bundle\AppBundle\DataService\TicketViewDataService;
use DeskPRO\Bundle\PortalBundle\Model\CommunityFilter;
use DeskPRO\Component\Util\MapUtils;
use Orb\Util\Arrays;
use Pagerfanta\Pagerfanta;

/**
 * Wrapper for loaders that users can call from Portal Home template.
 */
class HelpCenterData
{
    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * PublishDataHelper constructor.
     *
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Get news posts.
     *
     * Options:
     * - category: A category or category ID to get specifically
     * - count: How many items to get
     *
     * @param array $userOptions
     *
     * @return Pagerfanta
     */
    public function getNewsPosts(array $userOptions = [])
    {
        $options = array_merge([
            'category' => null,
            'count'    => 3,
        ], $userOptions);

        if (isset($options['category'])) {
            $options['category'] = $this->getNewsDataService()->getCategory($options['category']);
        }

        return $pager = $this->getNewsDataService()->getNewsPager(
            $options['category'],
            1,
            max(1, min(@$options['count'], 10)),
            $this->getUser()
        );
    }

    /**
     * Get a list of community forums with their counts.
     *
     * Options:
     * - orderByCount: True to order the list by count; default is to order by display_order
     *
     * @param array $userOptions
     *
     * @return array
     */
    public function getCommunityForumsWithCounts(array $userOptions = [])
    {
        $options = array_merge([
            'orderByCount' => false,
        ], $userOptions);

        $forums = $this->getCommunityDataService()->getCommunityForumsForPerson($this->getUser());

        $counts = $this->getCommunityDataService()->getItemsRepo()->countAllForumsGrouped();

        $ret = [];
        foreach ($forums as $forum) {
            $ret[$forum->getId()] = [
                'forum' => $forum,
                'count' => @$counts[$forum->getId()] ?: 0,
            ];
        }

        if ($options['orderByCount']) {
            return MapUtils::sortByFnValue($ret, function ($id, $v) {
                return $v['count'];
            });
        } else {
            return $ret;
        }
    }

    /**
     * @param array $userOptions
     *
     * @return Pagerfanta
     */
    public function getCommunityTopics(array $userOptions)
    {
        $options = array_merge([
            'count'   => 5,
            'orderBy' => 'date',
        ], $userOptions);

        return $this->getCommunityDataService()->getItemsPager(
            1,
            max(1, min(@$options['count'], 10)),
            new CommunityFilter([
                'sort'   => $options['orderBy'],
                'status' => 'all',
            ]),
            $this->getUser()
        );
    }

    /**
     * @param array $userOptions
     *
     * @return DownloadCategory[]
     */
    public function getDownloadsCategories(array $userOptions)
    {
        $options = array_merge([
            'count'    => 5,
            'category' => null,
        ], $userOptions);

        return $this->getDownloadsDataService()->getCategoryChildren($options['category'], $this->getUser());
    }

    public function getDownloads(array $userOptions)
    {
        $options = array_merge([
            'page'     => 1,
            'count'    => 10,
            'category' => null,
        ], $userOptions);

        return $this->getDownloadsDataService()->getDownloadsPager(
            $options['category'],
            (int) $options['page'],
            (int) $options['count'],
            $this->getUser()
        );
    }

    public function getRecentNews(array $userOptions)
    {
        $options = array_merge([
            'page'     => 1,
            'count'    => 5,
            'category' => null,
        ], $userOptions);

        return $this->getNewsDataService()->getNewsPager(
            $options['category'],
            (int) $options['page'],
            (int) $options['count'],
            $this->getUser()
        );
    }

    public function getUserInfo()
    {
        static $userInfo = null;

        if ($userInfo) {
            return $userInfo;
        }
        $user = $this->getUser();

        $authManager = $this->get('dp_authentication_manager.user');

        $userInfo = [
            'display_registration_link'     => $this->get('dp_authentication_manager.user')->isRegistrationFormVisible(),
            'chat_count'                    => $user ? $this->getChatDataService()->countUserChats($user, 'own') : 0,
            'ticket_count'                  => $user ? $this->getTicketsDataService()->getTicketCount($user, 'all') : 0,
            'ticket_count_org'              => $user ? $this->getTicketsDataService()->getOrganizationTicketCount($user, 'all') : 0,
            'user'                          => $user,
            'login_text_button_usersources' => $authManager->getLoginTextButtonUsersources(),
            'login_icon_usersources'        => $authManager->getLoginIconUsersources(),
            'show_forgot_password'          => $authManager->isForgotPasswordVisible(),
            'show_remember_me'              => $authManager->isRememberMeEnabled(),
            'show_login_form'               => $authManager->isLoginFormVisible(),
            'show_auth'                     => $authManager->isAuthVisible(),
        ];

        return $userInfo;
    }

    public function getTopics($guide)
    {
        $user = $this->getUser();

        $topics = $this->getGuidesDataService()->getGuideChildren(
            $guide,
            $user
        );

        $topics = Arrays::flattenHierarchy($topics);

        return array_values($topics);
    }

    public function getGuideTwoLevelSection($guide)
    {
        return $this->getGuidesDataService()->getGuideTwoLevelSection($guide);
    }

    /**
     * @return NewsDataService
     */
    private function getNewsDataService()
    {
        return $this->get('data.news');
    }

    /**
     * @return CommunityDataService
     */
    private function getCommunityDataService()
    {
        return $this->get('data.community');
    }

    /**
     * @return GuidesDataService
     */
    private function getGuidesDataService()
    {
        return $this->get('data.guides');
    }

    /**
     * @return ArticlesDataService
     */
    private function getArticlesDataService()
    {
        return $this->get('data.articles');
    }

    /**
     * @return TicketsDataService
     */
    private function getTicketsDataService()
    {
        return $this->get('data.tickets');
    }

    /**
     * @return ChatDataService
     */
    private function getChatDataService()
    {
        return $this->get('data.chat');
    }

    /**
     * @return DirectMessageThreadDataService
     */
    private function getDirectMessageThreadDataService()
    {
        return $this->get('data.direct_message.thread');
    }

    /**
     * @return TicketViewDataService
     */
    private function getTicketsViewService()
    {
        return $this->get('tickets.view');
    }

    private function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * @return DownloadsDataService
     */
    private function getDownloadsDataService()
    {
        return $this->get('data.downloads');
    }

    /**
     * @return Person|null
     */
    public function getUser()
    {
        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }

    public function getTicketsLayout()
    {
        $layouts = $this->container->getTicketLayoutManager()->getUserLayouts(true);

        return $layouts->compileJsObj();
    }
}
