<?php

namespace Application\DeskPRO\NewSearch\SearchEngine;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;

class SearchContextFactory
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @param Person $person
     *
     * @return SearchContextInterface
     */
    public function createUserSearchContext(Person $person)
    {
        $context = new SearchContext();

        if ($person && !$person->isGuest()) {
            $context->setPerson($person);
        }

        $context->setBrand($this->container->getBrandStack()->getActive()->getBrand());

        /** @var PortalPermissionsManager $permissionManager */
        $permissionManager = $this->container->get('portal_permissions_manager');
        $permissionBag     = $permissionManager->getPermissionsBagForPerson($person);

        if ($person->hasPerm('articles.use')) {
            $ids = $permissionBag->getAllowedArticleCategories();
            $context->setArticleCategoryIds($ids);
        }
        if ($person->hasPerm('feedback.use')) {
            $ids = $permissionBag->getAllowedFeedbackCategoryIds();
            $context->setFeedbackCategoryIds($ids);
        }
        if ($person->hasPerm('news.use')) {
            $ids = $permissionBag->getAllowedNewsCategories();
            $context->setNewsCategoryIds($ids);
        }
        if ($person->hasPerm('downloads.use')) {
            $ids = $permissionBag->getAllowedDownloadCategories();
            $context->setDownloadCategoryIds($ids);
        }
        if ($person->hasPerm('guides.use')) {
            $ids = $permissionBag->getAllowedGuides();
            $context->setGuideIds($ids);
        }

        return $context;
    }
}
