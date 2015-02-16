<?php

namespace Application\ImportBundle\Entity;

use Orb\Util\Strings;

/**
 * Basic properties on content
 *
 * Class AbstractContentEntity
 * @package Application\ImportBundle\Entity
 *
 * todo add slug validator
 */
abstract class AbstractContentEntity extends AbstractEntity implements ContentAwareInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $language;

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlug()
    {
        if ($this->slug) {
            return $this->slug;
        }

        return Strings::slugifyTitle($this->title);
    }

    /**
     * {@inheritdoc}
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }
}
