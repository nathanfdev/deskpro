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

namespace DeskPRO\Bundle\PortalBundle\View\PageTitle;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PageTitleGenerator
{
    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var PortalPermissionsManager
     */
    private $permissions_manager;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    public function __construct(LanguageManager $language_manager, BrandStack $brand_stack, PortalPermissionsManager $permissions_manager, TokenStorage $token_storage)
    {
        $this->language_manager    = $language_manager;
        $this->brand_stack         = $brand_stack;
        $this->permissions_manager = $permissions_manager;
        $this->token_storage       = $token_storage;
    }

    public function homepage()
    {
        return (string) $this->createHelpdeskTitleBuilder();
    }

    public function passwordReset($isResetting = true)
    {
        $builder = $this->createHelpdeskTitleBuilder();

        if ($isResetting) {
            $builder->prependSection($this->phrase('portal.account.section-title-reset-password'));
        } else {
            $builder->prependSection($this->phrase('portal.account.section-title-set-password'));
        }

        return (string) $builder;
    }

    public function loginPage()
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $builder->prependSection($this->phrase('portal.account.section-title-login'));

        return (string) $builder;
    }

    public function profile()
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $builder->prependSection($this->phrase('portal.account.section-title-your-account'));
        $builder->prependSection($this->phrase('portal.account.section-title-profile'));

        return (string) $builder;
    }

    public function profileEmails()
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $builder->prependSection($this->phrase('portal.account.section-title-your-account'));
        $builder->prependSection($this->phrase('portal.account.section-title-emails'));

        return (string) $builder;
    }

    public function register()
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $builder->prependSection($this->phrase('portal.account.section-title-register'));

        return (string) $builder;
    }

    public function tickets($ticket = null)
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $builder->prependSection($this->phrase('portal.tickets.section-title'));

        if ($ticket instanceof Ticket) {
            $builder->prependSection($ticket->getTitle());
        }

        return (string) $builder;
    }

    public function newticket()
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $builder->prependSection($this->phrase('portal.tickets.new-section-title'));

        return (string) $builder;
    }

    public function newticketGuestThankYou()
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $builder->prependSection($this->phrase('portal.tickets.guest-thanks-section-title'));

        return (string) $builder;
    }

    public function downloads($content_or_cat = null)
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $section_title = $this->phrase('portal.downloads.section-title');

        if ($content_or_cat instanceof DownloadCategory) {
            $builder->prependSection(
                $this->getCategorySection($content_or_cat, $section_title)
            );
        } elseif ($content_or_cat instanceof Download) {
            $builder->prependSection(
                $this->getCategorySection(
                    $content_or_cat->getCategory(),
                    $section_title
                )
            );
            $builder->prependSection($content_or_cat->getTitle());
        } else {
            $builder->prependSection($section_title);
        }

        return (string) $builder;
    }

    public function news($content_or_cat = null)
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $section_title = $this->phrase('portal.news.section-title');

        if ($content_or_cat instanceof NewsCategory) {
            $builder->prependSection(
                $this->getCategorySection($content_or_cat, $section_title)
            );
        } elseif ($content_or_cat instanceof News) {
            $builder->prependSection(
                $this->getCategorySection(
                    $content_or_cat->getCategory(),
                    $section_title
                )
            );
            $builder->prependSection($content_or_cat->getTitle());
        } else {
            $builder->prependSection($section_title);
        }

        return (string) $builder;
    }

    public function feedback($content_or_cat = null)
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $section_title = $this->phrase('portal.feedback.section-title');

        if ($content_or_cat instanceof FeedbackCategory) {
            $builder->prependSection(
                $this->getCategorySection($content_or_cat, $section_title)
            );
        } elseif ($content_or_cat instanceof Feedback) {
            $builder->prependSection(
                $this->getCategorySection(
                    $content_or_cat->getCategory(),
                    $section_title
                )
            );
            $builder->prependSection($content_or_cat->getTitle());
        } else {
            $builder->prependSection($section_title);
        }

        return (string) $builder;
    }

    public function kb($contentOrCat = null)
    {
        $builder      = $this->createHelpdeskTitleBuilder();
        $sectionTitle = $this->phrase('portal.articles.section-title');

        if ($contentOrCat instanceof ArticleCategory) {
            $builder->prependSection(
                $this->getCategorySection($contentOrCat, $sectionTitle)
            );
        } elseif ($contentOrCat instanceof Article) {
            $permission_bag = $this->getCurrentUserPermissionBag();
            $cat            = $contentOrCat->getPrimaryCategory();

            // if no access to this cat, try our best to loop to one he can see
            if (!$permission_bag->hasContentCategoryAccess($cat)) {
                foreach ($contentOrCat->getCategories() as $cat_to_check) {
                    if ($permission_bag->hasContentCategoryAccess($cat_to_check)) {
                        $cat = $cat_to_check;
                        break;
                    }
                }
            }

            if ($cat) {
                $builder->prependSection(
                    $this->getCategorySection(
                        $cat,
                        $sectionTitle
                    )
                );
            } else {
                $builder->prependSection([$sectionTitle]);
            }

            $builder->prependSection($contentOrCat->getTitle());
        } else {
            $builder->prependSection($sectionTitle);
        }

        return (string) $builder;
    }

    public function guides($topicOrGuide = null)
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $sectionTitle = $this->phrase('portal.guides.section-title');

        if ($topicOrGuide instanceof Guide) {
            $builder->prependSection(
                $this->getGuideSection($topicOrGuide, $sectionTitle)
            );
        } elseif ($topicOrGuide instanceof Topic) {
            $builder->prependSection(
                $this->getGuideSection(
                    $topicOrGuide->getGuide(),
                    $sectionTitle
                )
            );
            $builder->prependSection($topicOrGuide->getTitle());
        } else {
            $builder->prependSection($sectionTitle);
        }

        return (string) $builder;
    }

    public function search()
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $builder->prependSection($this->phrase('portal.general.search-section-title'));

        return (string) $builder;
    }

    public function labelSearch()
    {
        $builder = $this->createHelpdeskTitleBuilder();

        $builder->prependSection($this->phrase('portal.general.search-labels-section-title'));

        return (string) $builder;
    }

    public function createHelpdeskTitleBuilder()
    {
        $builder = new PageTitleBuilder();
        $name    = $this->setting('core.deskpro_name');
        $builder->appendSection($name);

        return $builder;
    }

    public function createEmptyBuilder()
    {
        return new PageTitleBuilder();
    }

    protected function getCategorySection(CategoryAbstract $cat, $prepend_to_section)
    {
        $section = [];

        $permission_bag = $this->getCurrentUserPermissionBag();
        if (!$permission_bag->hasContentCategoryAccess($cat)) {
            return $section;
        }

        if ($prepend_to_section) {
            $section[] = $prepend_to_section;
        }

        foreach ($cat->getTreeParents() as $parent) {
            $section[] = $this->language_manager->objectPhrase($parent);
        }

        $section[] = $this->language_manager->objectPhrase($cat);

        return $section;
    }

    protected function getGuideSection(Guide $guide, $prepend_to_section)
    {
        $section = [];

        $permission_bag = $this->getCurrentUserPermissionBag();
        if (!$permission_bag->hasContentCategoryAccess($guide)) {
            return $section;
        }

        if ($prepend_to_section) {
            $section[] = $prepend_to_section;
        }

        $section[] = $this->language_manager->objectPhrase($guide);

        return $section;
    }

    protected function phrase($phrase)
    {
        return $this->language_manager->getTranslator()->phrase($phrase);
    }

    protected function setting($name, $default = null)
    {
        return $this->brand_stack->getActive()->getSetting($name, $default);
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
