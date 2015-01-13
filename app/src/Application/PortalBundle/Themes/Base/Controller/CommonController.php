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


use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\People\PersonGuest;
use Application\PortalBundle\Annotation\Tag;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Request\TagRequest;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class CommonController extends AbstractController
{
    /**
     * @Tag(name="pager")
     *
     * @TagOptions(
     *      required={"pager"},
     *      defaults={
     *          "show_pagination": true
     *      },
     *      allowed_types={
     *          "pager":"Pagerfanta\Pagerfanta"
     *      }
     * )
     */
    public function pagerAction(TagRequest $request, array $options)
    {
        return $this->renderThemeView(
            'Theme:Common:pager.html.twig',
            array(
                'pager' => $options['pager'],
                'show_pagination' => $options['show_pagination']
            )
        );
    }

    /**
     * @Tag(name="get_in_touch")
     */
    public function getInTouchAction(TagRequest $tag_request)
    {
        return $this->renderThemeView('Theme:Common:get_in_touch.html.twig');
    }

    /**
     * @Tag(name="alerts")
     */
    public function alertsAction(TagRequest $tag_request)
    {
        return $this->renderThemeView('Theme:Common:alerts.html.twig');
    }

    /**
     * @Tag(name="related_content")
     * @TagOptions(
     *      required={"content_type", "content_id"},
     *      allowed_types={"content_type":"string", "content_id":{"string","int"}},
     *      allowed_values={"content_type":{"article","news","download","feedback"}}
     * )
     */
    public function relatedContentAction(TagRequest $tag_request, array $options)
    {
        $content_id = $options['content_id'];
        $content_type = $options['content_type'];

        if (!$content = $this->extractContent($content_type, $content_id)) {
            return new Response('');
        }

        $related_content_finder = new RelatedContentFinder($this->getUser() ?: new PersonGuest(), $content);
        $related_content = $related_content_finder->getRelatedEntities();

        return $this->render('Theme:Common:related_content.html.twig', array(
            'content_type' => $content_type,
            'content_id' => $content_id,
            'content' => $content,
            'related_content' => $related_content
        ));
    }

    /**
     * @Tag(name="flashes")
     */
    public function flashesAction(TagRequest $tag_request)
    {
        $flashes = array();
        $session = $tag_request->getSession();
        if (null !== $session && $session->isStarted()) {
            $flashes = $session->getFlashBag()->all();
        }

        return $this->renderThemeView(
            'Theme:Common:flashes.html.twig',
            array(
                'flashes' => $flashes
            )
        );
    }

    /**
     * @param $content_type
     * @param $content_id
     * @return Article|Download|Feedback|News|null
     */
    protected function extractContent($content_type, $content_id)
    {
        $content = null;
        switch ($content_type) {
            case Article::CONTENT_TYPE:
                $content = $this->getArticlesDataService()->getArticle($content_id);
                break;
            case Download::CONTENT_TYPE:
                $content = $this->getDownloadsDataService()->getDownload($content_id);
                break;
            case News::CONTENT_TYPE:
                $content = $this->getNewsDataService()->getPost($content_id);
                break;
            case Feedback::CONTENT_TYPE:
                $content = $this->getFeedbackDataService()->getItem($content_id);
                break;
        }
        return $content;
    }
}
