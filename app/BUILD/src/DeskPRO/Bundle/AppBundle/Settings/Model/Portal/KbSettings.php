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
}
