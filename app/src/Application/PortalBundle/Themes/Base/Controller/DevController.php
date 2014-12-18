<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Themes\Base\Controller;


use Application\PortalBundle\Annotation\Tag;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Request\TagRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevController extends AbstractController
{
    public function devAction()
    {
        return $this->render('Theme:Dev:dev.html.twig');
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
    public function renderAction(TagRequest $request, array $options)
    {
        return $this->render($options['tpl']);
    }
}
