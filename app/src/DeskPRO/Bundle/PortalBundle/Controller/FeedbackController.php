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
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\PortalBundle\Helper\FeedbackFilterUriHelper;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Bundle\PortalBundle\Model\FeedbackFilter;
use DeskPRO\Bundle\PortalBundle\Person\LoginRequiredException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zend\Feed\Writer\Extension\ITunes\Renderer\Feed;

class FeedbackController extends AbstractController
{
    /**
     * @Route("/feedback.{_format}", name="portal_feedback", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Security("is_granted('USE_FEEDBACK')")
     * @PageHttpCache()
     */
    public function indexAction(Request $request, $_format)
    {
        $page = $request->query->get('page', 1);

        //
        // RSS
        //
        if ('rss' === $_format) {
            $filter = new FeedbackFilter(array(
                'status'            => $request->query->get('status', 'all'),
                'status_categories' => $request->query->get('status_categories', array()),
                'types'             => $request->query->get('types', array()),
                'sort'              => $request->query->get('sort', 'date'),
                'sort_direction'    => $request->query->get('sort_direction', 'desc'),
            ));

            $pager = $this->getFeedbackDataService()->getItemsPager(
                $page,
                $request->query->get('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $filter
            );

            return $this->render('PortalBundle:Feedback:feed.rss.twig', array(
                'pager'    => $pager,
                'category' => null,
            ));
        }

        //
        // NEW FEEDBACK FORM
        //
        $rerendering_saved = $request->attributes->get('rerender-form', false); // true if auto-submit SavedFormController wants us to definitely rerender
        $is_saved_form = $request->attributes->get('saved-form', false); // true if auto-submit SavedFormController
        $person       = $this->getUser() ?: new PersonGuest();
        $new_feedback = new Feedback();
        $new_feedback->setPerson($person);
        $form = $this->createForm('new_feedback', $new_feedback, array(
            'person' => $person,
            'action' => $this->generateUrl('portal_feedback'),
            'allow_extra_fields' => $is_saved_form
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            if (
                !$rerendering_saved // if we are rerendering dont pass this condition
                &&
                (
                    !$form->getClickedButton() // if user clicked more_attachments dont pass condition
                    ||
                    (
                        $form->getClickedButton()
                        && $form->getClickedButton()->getConfig()->getName() !== "more_attachments"
                    )
                )
            ) {
                $new_feedback->setStatusCategory($this->getDefaultStatusCategory());
                $new_feedback->setStatus(Feedback::STATUS_ACTIVE);

                // deal with guests via negotiating with PersonFactory
                if ($person instanceof PersonGuest) {
                    try {
                        $person = $this->getPersonFactory()->createPersonFromGuest($person);
                    } catch (LoginRequiredException $e) {
                        $person = $e->getPerson();

                        return $this->getFormSaver()->saveFormForPerson($person, $form, $request);
                    }

                    // since the guest is set on the form, we need to update all of the associations
                    // TODO: we should be able to deal with this better by using a contact to beign with
                    $new_feedback->setPerson($person);
                    foreach ($new_feedback->getAttachments() as $attachment) {
                        $attachment->setPerson($person);
                    }
                }

                $this->persistAndFlushEntity($new_feedback);

                return $this->redirectToRoute('portal_feedback_view', array('slug' => $new_feedback->getSlug()));
            }
        }

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Feedback:index.html.twig',
            array(
                'page'            => $page,
                'count'           => $this->getBrandSetting('portal.per_page_content'),
                'show_pagination' => true,
                'form'            => $form->createView(),
                'user'            => $this->getUser(),
                'rerendering_saved'  => $rerendering_saved
            )
        );
    }

    /**
     * @Route("/feedback/browse/{filter_uri}", name="portal_feedback_browse", defaults={"query_path":""}, requirements={"filter_uri":".*"})
     *
     * @Method("GET")
     * @Security("is_granted('USE_FEEDBACK')")
     * @PageHttpCache()
     */
    public function browseAction(Request $request, $filter_uri)
    {
        $page = $request->query->get('page', 1);

        try {
            $uri_helper = new FeedbackFilterUriHelper();
            $filter     = $uri_helper->extractFeedbackFilter($filter_uri);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('filter_uri could not be parsed');
        }

        // order was incorrect, redirect them
        if ($filter_uri != $generated_uri = $uri_helper->generateUriSegment($filter)) {
            return $this->redirectToRoute('portal_feedback_browse', array('filter_uri' => $generated_uri), Response::HTTP_MOVED_PERMANENTLY);
        }

        $page_options = array(
            'page'              => $page,
            'count'             => $this->getBrandSetting('portal.per_page_content'),
            'show_pagination'   => true,
            'status'            => $filter->getStatus(),
            'status_categories' => $filter->getStatusCategories(),
            'types'             => $filter->getTypes(),
            'sort'              => $filter->getSort(),
            'sort_direction'    => $filter->getSortDirection(),
            'rerendering_saved' => false // wont happen here because we always rerender on index
        );

        if ($request->isXmlHttpRequest()) {
            return $this->renderThemeView(
                'Theme:Feedback:items_ajax_partial.html.twig',
                $page_options
            );
        }

        // setup and render an initial form that posts to /feedback
        $person       = $this->getUser() ?: new PersonGuest();
        $new_feedback = new Feedback();
        $new_feedback->setPerson($person);
        $form = $this->createForm('new_feedback', $new_feedback, array(
            'person' => $person,
            'action' => $this->generateUrl('portal_feedback'),
        ));

        $page_options = array_merge($page_options, array(
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ));

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Feedback:index.html.twig',
            $page_options
        );
    }

    /**
     * @Route("/feedback/view/{slug}", name="portal_feedback_view")
     * @ParamConverter(name="item", converter="deskpro_slug")
     * @Security("is_granted('USE_FEEDBACK') and is_granted('VIEW_FEEDBACK', item)")
     * @PageHttpCache(content="item")
     */
    public function viewAction(Request $request, Feedback $item)
    {
        //
        // COMMENT FORM
        //
        $new_comment_form = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_FEEDBACK)) {
            $form_handler = $this->get('form_handler.comment');
            $comment = new FeedbackComment();
            $new_comment_form = $form_handler->createForm($comment);
            if ($form_result = $form_handler->handle($new_comment_form, $request, $item, $comment)) {
                if ($form_result instanceof Response) {
                    return $form_result;
                }

                return $this->redirectToRoute('portal_feedback_view', array('slug' => $item->getSlug()));
            }
        }

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Feedback:view.html.twig',
            array(
                'item'             => $item,
                'content_id'       => $item->getId(),
                'content_type'     => Feedback::CONTENT_TYPE,
                'new_comment_form' => $new_comment_form ? $new_comment_form->createView() : null,
            )
        );
    }

    /**
     * @Route("/feedback/view/{slug}/vote-up", name="portal_feedback_vote_up", defaults={"up_or_down":"up"})
     * @Route("/feedback/view/{slug}/vote-down", name="portal_feedback_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="item", converter="deskpro_slug")
     * @Security("is_granted('USE_FEEDBACK') and is_granted('RATE_FEEDBACK', item)")
     */
    public function feedbackRateAction(Feedback $item, $visitor_id, $up_or_down)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($item, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($item, $visitor_id, $person);
        }

        return $this->redirectToRoute('portal_feedback_view', array('slug' => $item->getSlug()));
    }

    /**
     * @return \Application\DeskPRO\Entity\FeedbackStatusCategory
     */
    protected function getDefaultStatusCategory()
    {
        $default_status_category_id = $this->getBrandSetting('portal.default_feedback_status_category_id');
        $default_status_category    = $this->getFeedbackDataService()->getFeedbackStatusCategory($default_status_category_id);

        return $default_status_category;
    }
}
