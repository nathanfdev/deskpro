<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\Article as ArticleEntity;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ObjectLang;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Article.
 */
class Article extends ContentAbstract
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $contentInput = '';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $contentInputType = '';

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
     * Items attached to the content.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\ArticleAttachment>>")
     *
     * @var TicketAttachment[]
     */
    protected $attachments;

    /**
     * String array of labels associated with this article.
     *
     * @JMS\Expose()
     * @JMS\Type("ArrayCollection<Application\DeskPRO\Entity\LabelArticle>")
     *
     * @var ArrayCollection
     */
    protected $labels;

    /**
     * String array of labels associated with this article.
     *
     * @JMS\Expose()
     * @JMS\Type("ArrayCollection<Application\DeskPRO\Entity\CustomDataArticle>")
     *
     * @var ArrayCollection
     */
    protected $customData;

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
        $this->contentInput        = $entity->content_input;
        $this->contentInputType    = $entity->content_input_type;
        $this->attachments         = $entity->getAttachments();
        $this->labels              = $entity->getLabels();
        $this->customData          = $entity->getCustomData();
    }
}
