<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
