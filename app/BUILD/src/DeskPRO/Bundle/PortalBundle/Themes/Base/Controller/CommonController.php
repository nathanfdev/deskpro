<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Security\AgentImpersonateToken;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommonController.
 */
class CommonController extends AbstractController
{
    /**
     * @Tag(name="agent_bar", esi=true)
     */
    public function agentBarAction()
    {

        // AGENT IMPERSONATION

        $agent           = null;
        $impersonationOn = false;

        $token = $this->get('security.token_storage')->getToken();
        if ($token) {
            if ($token instanceof AgentImpersonateToken) {
                $agentId         = $token->getAttribute(AgentImpersonateToken::ATTR_AGENT_IMPERSONATE);
                $agent           = $this->getPersonDataService()->getPerson($agentId);
                $impersonationOn = true;
            } else {
                $agent = $this->getCurrentPerson();
            }
        }

        // no bar for a non-agent
        if (!$agent || !$agent->isAgent()) {
            return new Response('');
        }

        // no-agent-bar for focus window or preview
        $portalMode = $this->get('portal_mode_storage')->getMode();
        if ($portalMode && ($portalMode->isFocusWindow() || $portalMode->isAdminPreview())) {
            return new Response('');
        }

        return $this->renderThemeView(
            'Theme:Common:agent_bar.html.twig',
            [
                'impersonator'     => $agent,
                'impersonation_on' => $impersonationOn,
                'user'             => $this->getCurrentPerson(),
                'active_brand_id'  => $this->getBrandContainer()->getBrand()->getId(),
            ]
        );
    }

    /**
     * @Tag(name="alerts", esi=true)
     */
    public function alertsAction()
    {
        $user = $this->getUser();

        // DIFFERENT LANG

        $person   = $this->getCurrentPerson();
        $langDiff = false;
        if (!$person instanceof PersonGuest) {
            // user can click "dismiss" and we store a session var
            if (!$this->getSession()->get('ignore_language_warning', false)) {
                $activeLang = $this->get('language_stack')->getActiveOrDefault();
                $personLang = $person->getLanguage();

                if ($personLang) {
                    if ($personLang->getId() != $activeLang->getId()) {
                        $langDiff = [
                            'active_lang' => $activeLang,
                            'person_lang' => $personLang,
                        ];
                    }
                }
            }
        }

        // SAVED FORMS

        $saved_forms = [];
        if ($user && $all_saved = $this->getFormSaver()->getSavedForms($user)) {
            foreach ($all_saved as $saved) {
                // We don't want people to validate their email address without going through their mailbox
                if ($saved->getIntentionType() == SavedForm::INTENTION_VERIFY_EMAIL) {
                    continue;
                }
                $saved_forms[] = [
                    'message' => $this->getFormSaver()->getMessage($saved),
                    'link'    => $this->generateUrl('saved_form_auto_submit', ['auth_code' => $saved->getExternalCode()]),
                ];
            }
        }

        // TICKETS AWAITING REPLY

        $ticketsAwaitingReply = [];
        if (!$person instanceof PersonGuest) {
            /** @var \Application\DeskPRO\EntityRepository\Ticket $ticketRepo */
            $ticketRepo           = $this->getRepo(Ticket::class);
            $ticketsAwaitingReply = $ticketRepo->getWaitingForReplyForPerson($person, 3);
        }

        $should_display = count($saved_forms) || $langDiff || count($ticketsAwaitingReply);

        return $this->renderThemeView('Theme:Common:alerts.html.twig', [
            'user'                   => $user,
            'saved_forms'            => $saved_forms,
            'display_alerts'         => $should_display,
            'lang_diff'              => $langDiff,
            'tickets_awaiting_reply' => $ticketsAwaitingReply,
        ]);
    }

    /**
     * @Tag(name="flashes", esi=true)
     *
     * @param TagRequest $tag_request
     *
     * @return Response
     */
    public function flashesAction(TagRequest $tag_request)
    {
        $flashes = [];
        $session = $tag_request->getSession();
        if (null !== $session && $session->isStarted()) {
            $flashes = $session->getFlashBag()->all();
        }

        return $this->renderThemeView(
            'Theme:Common:flashes.html.twig',
            [
                'flashes' => $flashes,
            ]
        );
    }

    /**
     * @Tag(name="related_content", esi=true)
     * @TagHttpCache()
     *
     * @TagOptions(
     *      required={"content_type", "content_id"},
     *      allowed_types={"content_type":"string", "content_id":{"string","int"}},
     *      allowed_values={"content_type":{"article","news","download","feedback"}}
     * )
     *
     * @param array $options
     *
     * @return Response
     */
    public function relatedContentAction(TagRequest $tag_request, array $options)
    {
        $content_id   = $options['content_id'];
        $content_type = $options['content_type'];

        if (!$content = $this->extractContent($content_type, $content_id)) {
            return new Response('');
        }

        $related_content_finder = new RelatedContentFinder($this->getUser() ?: new PersonGuest(), $content);
        $related_content        = $related_content_finder->getRelatedEntities();

        $count = 0;
        foreach ($related_content as $type => $related) {
            $count += count($related);
        }

        return $this->render('Theme:Common:related_content.html.twig', [
            'content_type'    => $content_type,
            'content_id'      => $content_id,
            'content'         => $content,
            'related_count'   => $count,
            'related_content' => $related_content,
        ]);
    }

    /**
     * @param $content_type
     * @param $content_id
     *
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
