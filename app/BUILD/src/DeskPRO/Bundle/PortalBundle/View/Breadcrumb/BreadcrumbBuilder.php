<?php



namespace DeskPRO\Bundle\PortalBundle\View\Breadcrumb;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use DeskPRO\Bundle\PortalBundle\Themes\HelpCenter\HelpCenterTheme;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BreadcrumbBuilder
{
    /**
     * @var Breadcrumbs
     */
    private $breadcrumbs;

    /**
     * @var ObjectRouter
     */
    private $objectRouter;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var PortalBrandThemeLoader
     */
    private $brandThemeLoader;

    /**
     * Constructor.
     *
     * @param ObjectRouter           $objectRouter
     * @param UrlGeneratorInterface  $urlGenerator
     * @param LanguageManager        $languageManager
     * @param PortalBrandThemeLoader $brandThemeLoader
     */
    public function __construct(
        ObjectRouter $objectRouter,
        UrlGeneratorInterface $urlGenerator,
        LanguageManager $languageManager,
        PortalBrandThemeLoader $brandThemeLoader
    ) {
        $this->objectRouter     = $objectRouter;
        $this->urlGenerator     = $urlGenerator;
        $this->brandThemeLoader = $brandThemeLoader;
        $this->breadcrumbs      = new Breadcrumbs();

        if ($this->getThemeId() !== HelpCenterTheme::THEME_ID) {
            $this->breadcrumbs->add(
                $this->urlGenerator->generate('portal_home'),
                Breadcrumbs::PORTAL,
                ['phrase' => 'portal.general.nav-portal']
            );
        }
        $this->languageManager = $languageManager;
    }

    //####################################################################################################################
    // CHAT
    //####################################################################################################################

    public function addChat()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_chats'),
            Breadcrumbs::CHAT,
            ['phrase' => 'portal.general.nav-chat']
        );

        return $this;
    }

    public function addChatView(ChatConversation $chat)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($chat),
            Breadcrumbs::CHAT_VIEW,
            ['phrase' => 'portal.general.nav-chatlog']
        );

        return $this;
    }

    //####################################################################################################################
    // KB
    //####################################################################################################################

    public function addKb()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_kb'),
            Breadcrumbs::KB,
            ['phrase' => 'portal.general.nav-kb']
        );

        return $this;
    }

    public function addKbCat(ArticleCategory $cat)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($cat),
            Breadcrumbs::KB_CAT,
            ['title' => $this->languageManager->objectPhrase($cat)]
        );

        return $this;
    }

    public function addKbView(Article $a)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($a),
            Breadcrumbs::KB_VIEW,
            $a
        );

        return $this;
    }

    //####################################################################################################################
    // News
    //####################################################################################################################

    public function addNews()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_news'),
            Breadcrumbs::NEWS,
            ['phrase' => 'portal.general.nav-news']
        );

        return $this;
    }

    public function addNewsCat(NewsCategory $cat)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($cat),
            Breadcrumbs::NEWS_CAT,
            ['title' => $this->languageManager->objectPhrase($cat)]
        );

        return $this;
    }

    public function addNewsView(News $a)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($a),
            Breadcrumbs::NEWS_VIEW,
            $a
        );

        return $this;
    }

    //####################################################################################################################
    // Downloads
    //####################################################################################################################

    public function addDownloads()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_downloads'),
            Breadcrumbs::DOWNLOADS,
            ['phrase' => 'portal.general.nav-downloads']
        );

        return $this;
    }

    public function addDownloadCat(DownloadCategory $cat)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($cat),
            Breadcrumbs::DOWNLOADS_CAT,
            ['title' => $this->languageManager->objectPhrase($cat)]
        );

        return $this;
    }

    public function addDownloadView(Download $a)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($a),
            Breadcrumbs::DOWNLOADS_VIEW,
            $a
        );

        return $this;
    }

    //####################################################################################################################
    // Guides
    //####################################################################################################################

    public function addTopics()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_guides'),
            Breadcrumbs::TOPICS,
            ['phrase' => 'portal.general.nav-guides']
        );

        return $this;
    }

    public function addGuide(Guide $guide)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($guide),
            Breadcrumbs::GUIDE,
            ['title' => $this->languageManager->objectPhrase($guide)]
        );

        return $this;
    }

    public function addTopic(Topic $topic)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($topic),
            Breadcrumbs::TOPIC,
            $topic
        );

        return $this;
    }

    //####################################################################################################################
    // Profile / Registration
    //####################################################################################################################

    public function addYourAccount()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_user_profile'),
            Breadcrumbs::PROFILE,
            ['phrase' => 'portal.general.nav-your-account']
        );

        return $this;
    }

    public function addProfile()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_user_profile'),
            Breadcrumbs::PROFILE,
            ['phrase' => 'portal.general.nav-profile']
        );

        return $this;
    }

    public function addEditEmails()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_user_profile_emails'),
            Breadcrumbs::PROFILE_EMAILS,
            ['phrase' => 'portal.general.nav-emails']
        );

        return $this;
    }

    public function addRegistration()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_user_registration'),
            Breadcrumbs::REGISTER,
            ['phrase' => 'portal.general.nav-register']
        );

        return $this;
    }

    public function addLogin()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_login'),
            Breadcrumbs::LOGIN,
            ['phrase' => 'portal.general.nav-login']
        );

        return $this;
    }

    public function addPasswordReset()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_reset_password'),
            Breadcrumbs::PASSWORD_RESET,
            ['phrase' => 'portal.general.nav-reset-password']
        );

        return $this;
    }

    public function addPasswordSet()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_set_password'),
            Breadcrumbs::PASSWORD_SET,
            ['phrase' => 'portal.general.nav-set-password']
        );

        return $this;
    }

    //####################################################################################################################
    // Search
    //####################################################################################################################

    public function addSearch($query)
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_search', ['q' => $query]),
            Breadcrumbs::SEARCH,
            ['phrase' => 'portal.general.search-section-title']
        );

        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_search', ['q' => $query]),
            Breadcrumbs::SEARCH,
            ['name' => sprintf('"%s"', $query)]
        );

        return $this;
    }

    public function addLabelSearch($type, $label)
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_search_labels', ['type' => $type, 'label' => $label]),
            Breadcrumbs::SEARCH,
            ['phrase' => 'portal.general.search-labels-section-title']
        );

        if ($label) {
            $this->breadcrumbs->add(
                $this->urlGenerator->generate('portal_search_labels', ['type' => $type, 'label' => $label]),
                Breadcrumbs::SEARCH,
                ['name' => sprintf('"%s"', $label)]
            )
            ;
        }

        return $this;
    }

    //####################################################################################################################
    // Community
    //####################################################################################################################

    public function addCommunity()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_community'),
            Breadcrumbs::COMMUNITY,
            ['phrase' => 'portal.general.nav-community']
        );

        return $this;
    }

    public function addCommunityView(CommunityTopic $a)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($a),
            Breadcrumbs::COMMUNITY_VIEW,
            $a
        );

        return $this;
    }

    public function addCommunityForum(CommunityForum $a)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($a),
            $a->getTitle(),
            $a
        );

        return $this;
    }

    public function addCommunityCreate(CommunityForum $a)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($a),
            $a->getTitle(),
            $a
        );

        return $this;
    }

    //####################################################################################################################
    // Members
    //####################################################################################################################
    public function addMembersList()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_members'),
            Breadcrumbs::MEMBERS,
            ['phrase' => 'portal.general.nav-members']
        );

        return $this;
    }

    //####################################################################################################################
    // Direct message
    //####################################################################################################################
    public function addDirectMessagesList()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_dm'),
            Breadcrumbs::DIRECT_MESSAGES,
            ['phrase' => 'portal.general.nav-dm']
        );

        return $this;
    }

    //####################################################################################################################
    // Tickets
    //####################################################################################################################

    /**
     * @return $this
     */
    public function addNewTicket()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_new_ticket'),
            Breadcrumbs::TICKETS_NEW,
            ['phrase' => 'portal.general.nav-newticket']
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addTicketList()
    {
        $this->breadcrumbs->add(
            $this->urlGenerator->generate('portal_tickets'),
            Breadcrumbs::TICKETS,
            ['phrase' => 'portal.general.nav-tickets']
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addTicketApprovalList()
    {
        if ($this->getThemeId() === HelpCenterTheme::THEME_ID) {
            $phrase = 'helpcenter.approvals.my-approvals';
        } else {
            $phrase = 'portal.general.nav-approvals';
        }

        $this->breadcrumbs->add(
            $this->urlGenerator->generate('ticket_approvals'),
            Breadcrumbs::TICKETS,
            ['phrase' => $phrase]
        );

        return $this;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function addTicketView(Ticket $ticket)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($ticket),
            Breadcrumbs::TICKETS_VIEW,
            $ticket
        );

        return $this;
    }

    /**
     * @param TicketApproval $approval
     *
     * @return $this
     */
    public function addApprovalView(TicketApproval $approval)
    {
        $this->breadcrumbs->add(
            $this->objectRouter->getPortalPath($approval),
            Breadcrumbs::APPROVALS_VIEW,
            $approval
        );

        return $this;
    }

    /**
     * @return Breadcrumbs
     */
    public function done()
    {
        return $this->breadcrumbs;
    }

    /**
     * @return string
     */
    private function getThemeId()
    {
        return $this->brandThemeLoader->getPortalBrandTheme()->getActiveTheme()->getId();
    }
}
