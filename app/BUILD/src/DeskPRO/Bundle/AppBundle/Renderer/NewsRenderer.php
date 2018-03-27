<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Renderer;

use Application\DeskPRO\Entity\News;

class NewsRenderer
{
    /**
     * @param News $post
     *
     * @return string
     */
    public function render(News $post)
    {
        return $post->getContentHtml();
    }

    /**
     * @param News $post
     *
     * @return string
     */
    public function renderExceprt(News $post)
    {
        return $post->getExcerptHtml();
    }
}
