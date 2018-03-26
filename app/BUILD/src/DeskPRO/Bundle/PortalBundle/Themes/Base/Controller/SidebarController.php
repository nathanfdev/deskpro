<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class SidebarController extends AbstractController
{
    /**
     * @Tag(name="kb_top_articles_detail", default_options={"style":"detail"}, esi=true)
     * @Tag(name="kb_top_articles", default_options={"style":"simple"}, esi=true)
     *
     * @TagOptions(
     *      defaults={
     *          "category":null,
     *          "style": "simple",
     *          "page": 1,
     *          "count": 5,
     *          "show_category_link": false
     *      },
     *      inherit_from={"articles_options"},
     *      allowed_values={
     *          "style": {"detail", "simple"}
     *      }
     * )
     *
     * @Security("is_granted('USE_ARTICLES')")
     * @TagHttpCache()
     */
    public function topArticlesAction(TagRequest $tag_request, array $options)
    {
        $person = $this->getCurrentPerson();
        $pager  = $this->getArticlesDataService()->getTopArticlesPager($options['page'], $options['count'], $person);

        return $this->renderThemeView(
            sprintf('Theme:Sidebar:TopArticles/%s.html.twig', $options['style']),
            [
                'pager'              => $pager,
                'category'           => null,
                'show_category_link' => $options['show_category_link'],
            ]
        );
    }

    /**
     * @Tag(name="customer_satisfaction", esi=true)
     *
     * @TagOptions(
     *      defaults={
     *          "style": "icons",
     *      },
     *      allowed_values={
     *          "style": {"icons"}
     *      }
     * )
     *
     * @TagHttpCache()
     */
    public function customerSatisfactionAction(TagRequest $tag_request, array $options)
    {
        $resolved_tickets = $this->getTicketsDataService()->getLatestResolvedTickets(20);

        // harded the view by only passing it safe view data we need
        $ratings = array_map(function (Ticket $ticket) {
            $rating = (int) $ticket->getFeedbackRating();

            return [
                'rating' => $rating,
            ];
        }, $resolved_tickets);

        // reduce the ratings info into a count of happy ticket owners
        $satisfied_count = array_reduce($ratings, function ($count, $ratings_array) {
            if ($ratings_array['rating'] > 0) {
                return $count + 1;
            }

            return $count;
        }, 0);

        return $this->renderThemeView(
            sprintf('Theme:Sidebar:Satisfaction/%s.html.twig', $options['style']),
            [
                'ratings'         => $ratings,
                'satisfied_count' => $satisfied_count,
                'total_ratings'   => count($ratings),
            ]
        );
    }

    /**
     * @Tag(name="agents_online", default_options={"style":"list"}, esi=true)
     * @Tag(name="agents_online_small", default_options={"style":"small"}, esi=true)
     *
     * @TagOptions(
     *      defaults={
     *          "style": "list",
     *      },
     *      allowed_values={
     *          "style": {"list", "small"}
     *      }
     * )
     *
     * @TagHttpCache()
     */
    public function onlineAgentsAction(TagRequest $tag_request, array $options)
    {
        $cutoff             = date('Y-m-d H:i:s', time() - $this->getBrandSetting('core_chat.agent_timeout'));
        $online_agent_query = $this->getEm()->createQueryBuilder();
        $online_agent_query->select('s')->from('DeskPRO:Session', 's');
        $online_agent_query->leftJoin('s.person', 'p');
        $online_agent_query->where('p.is_agent = true');
        $online_agent_query->andWhere('s.is_chat_available = true');
        $online_agent_query->andWhere('s.date_last > :cutoff');
        $online_agent_query->setParameter('cutoff', new \DateTime($cutoff));

        $online_agent_sessions = $online_agent_query->getQuery()->getResult();
        $online_agents         = array_map(function (Session $session) {
            return $session->getPerson();
        }, $online_agent_sessions);

        $agent_ids            = [];
        $unique_online_agents = array_filter($online_agents, function (Person $person) use (&$agent_ids) {
            $id = $person->getId();
            if (!in_array($id, $agent_ids) && $person->isAgent()) {
                $agent_ids[] = $id;

                return true;
            }

            return false;
        });

        return $this->renderThemeView(
            sprintf('Theme:Sidebar:AgentsOnline/%s.html.twig', $options['style']),
            [
                'online_agents'       => $unique_online_agents,
                'online_agents_count' => count($unique_online_agents),
            ]
        );
    }

    /**
     * @Tag(name="news_sidebar", default_options={"style":"recent"}, esi=true)
     * @Tag(name="news_sidebar_dates", default_options={"style":"dates"}, esi=true)
     *
     * @TagOptions(
     *      defaults={
     *          "style": "recent",
     *      },
     *      allowed_values={
     *          "style": {"dates", "recent"}
     *      }
     * )
     * @TagHttpCache()
     */
    public function newsAction(TagRequest $tag_request, array $options)
    {
        return $this->renderThemeView(
            sprintf('Theme:Sidebar:News/%s.html.twig', $options['style'])
        );
    }
}
