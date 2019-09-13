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
     * Get a list of community channels with their counts.
     *
     * Options:
     * - orderByCount: True to order the list by count; default is to order by display_order
     *
     * @param array $userOptions
     *
     * @return array
     */
    public function getCommunityChannelsWithCounts(array $userOptions = [])
    {
        $options = array_merge([
            'orderByCount' => false,
        ], $userOptions);

        $channels = $this->getCommunityDataService()->getCommunityChannelsForPerson($this->getUser());

        $counts = $this->getCommunityDataService()->getItemsRepo()->countAllChannelsGrouped();

        $ret = [];
        foreach ($channels as $channel) {
            $ret[$channel->getId()] = [
                'channel' => $channel,
                'count'   => @$counts[$channel->getId()] ?: 0,
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
}
