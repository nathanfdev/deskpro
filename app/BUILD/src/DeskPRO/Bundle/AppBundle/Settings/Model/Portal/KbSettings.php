<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Portal;

use JMS\Serializer\Annotation as JMS;

/**
 * Class KbSettings.
 */
class KbSettings extends AbstractAppSettings
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $knowledgebaseDeepTree;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $featuredArticles;

    /**
     * @return bool
     */
    public function isKnowledgebaseDeepTree()
    {
        return $this->knowledgebaseDeepTree;
    }

    /**
     * @param bool $knowledgebaseDeepTree
     *
     * @return KbSettings
     */
    public function setKnowledgebaseDeepTree($knowledgebaseDeepTree)
    {
        $this->knowledgebaseDeepTree = $knowledgebaseDeepTree;

        return $this;
    }

    /**
     * @return string
     */
    public function getFeaturedArticles()
    {
        return $this->featuredArticles;
    }

    /**
     * @param string $featuredArticles
     *
     * @return KbSettings
     */
    public function setFeaturedArticles($featuredArticles)
    {
        $this->featuredArticles = $featuredArticles;

        return $this;
    }
}
