<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Language;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TextSnippetContent.
 */
class TextSnippetContent
{
    /**
     * @var Language
     *
     * @JMS\Exclude()
     */
    protected $language;

    /**
     * Snippet title.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $title;

    /**
     * Snippet content.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $content;

    /**
     * Constructor.
     *
     * @param Language $language
     */
    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->language->getLocale();
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
