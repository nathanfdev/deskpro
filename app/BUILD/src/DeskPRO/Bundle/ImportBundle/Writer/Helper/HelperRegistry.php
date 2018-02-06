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

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HelperRegistry.
 */
class HelperRegistry
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $helpers = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $helpers
     */
    public function setHelpers(array $helpers)
    {
        $this->helpers = $helpers;
    }

    /**
     * @param string $helperClass
     *
     * @return object
     */
    public function getHelper($helperClass)
    {
        if (!isset($this->helpers[$helperClass])) {
            throw new \RuntimeException("Helper `$helperClass` not found");
        }

        return $this->container->get($this->helpers[$helperClass]);
    }

    /**
     * @return BlobAdapter
     */
    public function getBlobAdapter()
    {
        return $this->getHelper(BlobAdapter::class);
    }

    /**
     * @return AttachmentHelper
     */
    public function getAttachmentHelper()
    {
        return $this->getHelper(AttachmentHelper::class);
    }

    /**
     * @return CategoryHelper
     */
    public function getCategoryHelper()
    {
        return $this->getHelper(CategoryHelper::class);
    }

    /**
     * @return CreateEntityHelper
     */
    public function getCreateEntityHelper()
    {
        return $this->getHelper(CreateEntityHelper::class);
    }

    /**
     * @return BrandHelper
     */
    public function getBrandHelper()
    {
        return $this->getHelper(BrandHelper::class);
    }

    /**
     * @return DepartmentHelper
     */
    public function getDepartmentHelper()
    {
        return $this->getHelper(DepartmentHelper::class);
    }

    /**
     * @return CustomDataHelper
     */
    public function getCustomDataHelper()
    {
        return $this->getHelper(CustomDataHelper::class);
    }

    /**
     * @return PersonContactDataHelper
     */
    public function getPersonContactDataHelper()
    {
        return $this->getHelper(PersonContactDataHelper::class);
    }

    /**
     * @return OrganizationContactDataHelper
     */
    public function getOrganizationContactDataHelper()
    {
        return $this->getHelper(OrganizationContactDataHelper::class);
    }

    /**
     * @return CommentHelper
     */
    public function getCommentHelper()
    {
        return $this->getHelper(CommentHelper::class);
    }

    /**
     * @return PersonHelper
     */
    public function getPersonHelper()
    {
        return $this->getHelper(PersonHelper::class);
    }

    /**
     * @return OrganizationHelper
     */
    public function getOrganizationHelper()
    {
        return $this->getHelper(OrganizationHelper::class);
    }

    /**
     * @return LabelHelper
     */
    public function getLabelHelper()
    {
        return $this->getHelper(LabelHelper::class);
    }

    /**
     * @return LanguageHelper
     */
    public function getLanguageHelper()
    {
        return $this->getHelper(LanguageHelper::class);
    }

    /**
     * @return TranslationHelper
     */
    public function getTranslationHelper()
    {
        return $this->getHelper(TranslationHelper::class);
    }

    /**
     * @return UserGroupHelper
     */
    public function getUserGroupHelper()
    {
        return $this->getHelper(UserGroupHelper::class);
    }

    /**
     * @return TextSnippetCategoryHelper
     */
    public function getTextSnippetCategoryHelper()
    {
        return $this->getHelper(TextSnippetCategoryHelper::class);
    }
}
