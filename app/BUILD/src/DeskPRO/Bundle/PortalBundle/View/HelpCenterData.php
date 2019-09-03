<?php

namespace DeskPRO\Bundle\PortalBundle\View;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\PortalBundle\Model\CommunityFilter;
use DeskPRO\Component\Util\MapUtils;

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
     * @return \Pagerfanta\Pagerfanta
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
     * @return \Pagerfanta\Pagerfanta
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
     * @return \DeskPRO\Bundle\AppBundle\DataService\NewsDataService
     */
    private function getNewsDataService()
    {
        return $this->get('data.news');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\Community\CommunityDataService
     */
    private function getCommunityDataService()
    {
        return $this->get('data.community');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\GuidesDataService
     */
    private function getGuidesDataService()
    {
        return $this->get('data.guides');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\ArticlesDataService
     */
    private function getArticlesDataService()
    {
        return $this->get('data.articles');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\TicketsDataService
     */
    private function getTicketsDataService()
    {
        return $this->get('data.tickets');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\Chat\ChatDataService
     */
    private function getChatDataService()
    {
        return $this->get('data.chat');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\DirectMessageThreadDataService
     */
    private function getDirectMessageThreadDataService()
    {
        return $this->get('data.direct_message.thread');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\TicketViewDataService
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
     * @return Person|null
     */
    public function getUser()
    {
        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }
}
