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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\Article as ArticleEntity;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ObjectLang;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Article.
 */
class Article extends ContentAbstract
{
    /**
     * Content category.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\ArticleCategory>>")
     *
     * @var ArticleCategory[]
     */
    protected $categories;

    /**
     * The article category names.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $categoryNames;

    /**
     * Article title translations.
     *
     * @JMS\Type("array<Application\DeskPRO\Entity\ObjectLang>")
     *
     * @var ObjectLang[]
     */
    protected $titleTranslations;

    /**
     * Article content translations.
     *
     * @JMS\Type("array<Application\DeskPRO\Entity\ObjectLang>")
     *
     * @var ObjectLang[]
     */
    protected $contentTranslations;

    /**
     * Constructor.
     *
     * @param ArticleEntity $entity
     */
    public function __construct(ArticleEntity $entity)
    {
        parent::__construct($entity);
        $this->categories          = $entity->getCategories();
        $this->categoryNames       = $entity->getCategoryNames();
        $this->titleTranslations   = $entity->getTitleTranslations();
        $this->contentTranslations = $entity->getContentTranslations();
    }
}
