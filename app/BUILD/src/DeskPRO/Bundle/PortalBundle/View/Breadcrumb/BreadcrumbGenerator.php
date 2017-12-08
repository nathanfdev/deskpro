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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\View\Breadcrumb;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BreadcrumbGenerator
{
    /**
     * @var PortalPermissionsManager
     */
    private $permissions_manager;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    /**
     * @var ObjectRouter
     */
    private $object_router;

    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    public function __construct(PortalPermissionsManager $permissions_manager, TokenStorage $token_storage, ObjectRouter $object_router, UrlGeneratorInterface $url_generator, LanguageManager $language_manager)
    {
        $this->permissions_manager = $permissions_manager;
        $this->token_storage       = $token_storage;
        $this->object_router       = $object_router;
        $this->url_generator       = $url_generator;
        $this->language_manager    = $language_manager;
    }

    /**
     * @return BreadcrumbBuilder
     */
    public function createBuilder()
    {
        return new BreadcrumbBuilder($this->object_router, $this->url_generator, $this->language_manager);
    }

    //####################################################################################################################
    // CHAT
    //####################################################################################################################

    public function buildChat()
    {
        return $this->createBuilder()->addChat()->done();
    }

    public function buildChatConversation(ChatConversation $chat)
    {
        return $this->createBuilder()->addChat()->addChatView($chat)->done();
    }

    //####################################################################################################################
    // KB
    //####################################################################################################################

    public function buildKb()
    {
        return $this->createBuilder()->addKb()->done();
    }

    public function buildKbCategory(ArticleCategory $cat)
    {
        return $this->createKbCategoryBuilder($cat)->done();
    }

    protected function createKbCategoryBuilder(ArticleCategory $cat)
    {
        $b = $this->createBuilder()->addKb();

        // we have to check permissions, because a user can be viewing an article
        // he has access to, but does not have access to the primary category
        $permission_bag = $this->getCurrentUserPermissionBag();

        foreach ($cat->getTreeParents() as $c) {
            if ($permission_bag->hasContentCategoryAccess($c)) {
                $b->addKbCat($c);
            }
        }

        // check here, too
        if ($permission_bag->hasContentCategoryAccess($cat)) {
            $b->addKbCat($cat);
        }

        return $b;
    }

    public function buildKbArticle(Article $a)
    {
        // we have to check permissions, because a user can be viewing an article
        // he has access to, but does not have access to the primary category
        $permission_bag = $this->getCurrentUserPermissionBag();

        $cat = $a->getPrimaryCategory();

        // if no access to this cat, try our best to loop to one he can see
        if (!$permission_bag->hasContentCategoryAccess($cat)) {
            foreach ($a->getCategories() as $cat_to_check) {
                if ($permission_bag->hasContentCategoryAccess($cat_to_check)) {
                    $cat = $cat_to_check;
                    break;
                }
            }
        }

        if ($cat) {
            $builder = $this->createKbCategoryBuilder($cat);
        } else {
            $builder = $this->createBuilder()->addKb();
        }

        return $builder->addKbView($a)->done();
    }

    //####################################################################################################################
    // News
    //####################################################################################################################

    public function buildNews()
    {
        return $this->createBuilder()->addNews()->done();
    }

    public function buildNewsCategory(NewsCategory $cat)
    {
        return $this->createNewsCategoryBuilder($cat)->done();
    }

    protected function createNewsCategoryBuilder(NewsCategory $cat)
    {
        $b = $this->createBuilder()->addNews();

        foreach ($cat->getTreeParents() as $c) {
            $b->addNewsCat($c);
        }

        $b->addNewsCat($cat);

        return $b;
    }

    public function buildNewsPost(News $a)
    {
        return $this->createNewsCategoryBuilder($a->getCategory())
            ->addNewsView($a)
            ->done();
    }

    //####################################################################################################################
    // Downloads
    //####################################################################################################################

    public function buildDownloads()
    {
        return $this->createBuilder()->addDownloads()->done();
    }

    public function buildDownloadsCategory(DownloadCategory $cat)
    {
        return $this->createDownloadsCategoryBuilder($cat)->done();
    }

    protected function createDownloadsCategoryBuilder(DownloadCategory $cat)
    {
        $b = $this->createBuilder()->addDownloads();

        foreach ($cat->getTreeParents() as $c) {
            $b->addDownloadCat($c);
        }

        $b->addDownloadCat($cat);

        return $b;
    }

    public function buildDownloadsFile(Download $a)
    {
        return $this->createDownloadsCategoryBuilder($a->getCategory())
            ->addDownloadView($a)
            ->done();
    }

    //####################################################################################################################
    // Guides
    //####################################################################################################################

    public function buildGuides()
    {
        return $this->createBuilder()->addTopics()->done();
    }

    public function buildGuide(Guide $guide)
    {
        return $this->createGuideBuilder($guide)->done();
    }

    protected function createGuideBuilder(Topic $topic)
    {
        $b = $this->createBuilder()->addTopic($topic);

        foreach ($topic->getTreeParents() as $c) {
            $b->addTopic($c);
        }

        $b->addGuide($topic->getGuide());

        return $b;
    }

    public function buildTopicsFile(Topic $a)
    {
        return $this->createGuideBuilder($a)
            ->done();
    }

    //####################################################################################################################
    // Feedback
    //####################################################################################################################

    public function buildFeedback()
    {
        return $this->createBuilder()->addFeedback()->done();
    }

    public function buildFeedbackView(Feedback $a)
    {
        return $this->createBuilder()->addFeedback()
            ->addFeedbackView($a)
            ->done();
    }

    //####################################################################################################################
    // Search
    //####################################################################################################################

    public function buildSearch($query)
    {
        return $this->createBuilder()->addSearch($query)->done();
    }

    public function buildLabelSearch($type, $label)
    {
        return $this->createBuilder()->addLabelSearch($type, $label)->done();
    }

    //####################################################################################################################
    // Tickets
    //####################################################################################################################

    public function buildNewTicket()
    {
        return $this->createBuilder()->addNewTicket()->done();
    }

    public function buildNewTicketGuestThankYou()
    {
        return $this->createBuilder()->addNewTicket()->done();
    }

    public function buildTicketList()
    {
        return $this->createBuilder()->addTicketList()->done();
    }

    public function buildTicketView(Ticket $t)
    {
        return $this->createBuilder()->addTicketList()
            ->addTicketView($t)
            ->done();
    }

    public function buildTicketEdit(Ticket $t)
    {
        return $this->buildTicketView($t);
    }

    //####################################################################################################################
    // Profile
    //####################################################################################################################

    public function buildRegistration()
    {
        return $this->createBuilder()->addRegistration()->done();
    }

    public function buildProfile()
    {
        return $this->createBuilder()->addYourAccount()->addProfile()->done();
    }

    public function buildProfileEmails()
    {
        return $this->createBuilder()->addYourAccount()->addEditEmails()->done();
    }

    public function buildLogin()
    {
        return $this->createBuilder()->addLogin()->done();
    }

    public function buildPasswordReset($isResetting = true)
    {
        if (!$isResetting) {
            return $this->createBuilder()->addPasswordSet()->done();
        }

        return $this->createBuilder()->addPasswordReset()->done();
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsBag
     */
    protected function getCurrentUserPermissionBag()
    {
        if (null === $token = $this->token_storage->getToken()) {
            $person = null;
        }

        if (!is_object($person = $token->getUser())) {
            // e.g. anonymous authentication
            $person = null;
        }

        if ($person) {
            return $this->permissions_manager->getPermissionsBagForPerson($person);
        }

        return $this->permissions_manager->getPermissionsBagForGuest();
    }
}
