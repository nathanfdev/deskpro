<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class DevController extends AbstractController
{
    /**
     * @Cache(smaxage="10 minutes")
     */
    public function devAction()
    {
        return $this->renderThemeView('Theme:Dev:dev.html.twig');
    }

    /**
     * @Tag(name="statisfaction_stats_list", default_options={"tpl":"Theme:Portal:Dev/satisfaction_stats_list.html.twig"})
     * @Tag(name="agents_online_list", default_options={"tpl":"Theme:Portal:Dev/agents_online_list.html.twig"})
     * @Tag(name="news_date_list", default_options={"tpl":"Theme:News:Dev/news_date_list.html.twig"})
     *
     * @TagOptions(
     *      required={"tpl"}
     * )
     */
    public function renderAction(TagRequest $tag_request, array $options)
    {
        return $this->renderThemeView($options['tpl']);
    }
}
