<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Security\AgentImpersonateToken;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Person\PersonValidator;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Symfony\Component\HttpFoundation\Response;

class CommonController extends AbstractController
{
    /**
     * @Tag(name="get_in_touch")
     * @TagHttpCache()
     */
    public function getInTouchAction(TagRequest $tag_request)
    {
        return $this->renderThemeView('Theme:Common:get_in_touch.html.twig');
    }

    /**
     * DO NOT use always_guest_inline=true on this or we risk cache alert messages to guests.
     *
     * @Tag(name="agent_bar", esi=true)
     */
    public function agentBarAction(TagRequest $tag_request)
    {
        //
        // AGENT IMPERSONATION
        //
        $agent            = null;
        $impersonation_on = false;
        if ($token = $this->get('security.token_storage')->getToken()) {
            if ($token instanceof AgentImpersonateToken) {
                $agent_id         = $token->getAttribute(AgentImpersonateToken::ATTR_AGENT_IMPERSONATE);
                $agent            = $this->getPersonDataService()->getPerson($agent_id);
                $impersonation_on = true;
            } else {
                $agent = $this->getCurrentPerson();
            }
        }

        // no bar for a non-agent
        if (!$agent || !$agent->isAgent()) {
            return new Response('');
        }

        //
        // TEMPORARY: brand dropdown info - this wil be replaced with something more robust
        //
        $brands = $this->getEm()->getRepository('DeskPRO:Brand')->findAll();
        /** @var \Application\DeskPRO\Entity\Brand $brand */
        $b = [];
        foreach ($brands as $brand) {
            $b[] = [
                'brand'    => $brand,
                'id'       => $brand->getId(),
                'name'     => $brand->getName(),
                'theme_id' => $brand->getThemeSet()->getThemeId(),
            ];
        }

        return $this->renderThemeView(
            'Theme:Common:agent_bar.html.twig',
            array(
                'impersonator'     => $agent,
                'impersonation_on' => $impersonation_on,
                'user'             => $this->getCurrentPerson(),
                'brands'           => $b,
                'active_brand_id'  => $this->getBrandContainer()->getBrand()->getId(),
            )
        );
    }

    /**
     * DO NOT use always_guest_inline=true on this or we risk cache alert messages to guests.
     *
     * @Tag(name="alerts", esi=true)
     */
    public function alertsAction(TagRequest $tag_request)
    {
        $user = $this->getUser();

        // TODO: this controller can be refactored into a module that collects alerts, but
        //       we'll keep the code here until we figure out all of the different alerts

        //
        // ACCOUNT VALIDATION
        //
        $person_validator  = $this->get('person.portal_validator');
        $validation_alerts = array();
        if ($user && !$user->isUserValid()) {
            $primary_email = $user->getPrimaryEmail();
            if (!$user->isEmailValidated()) {
                $validation_alerts[] = array(
                    'type'            => PersonValidator::TYPE_EMAIL_PRIMARY,
                    'message'         => $this->phrase('portal.account.validation_alert',
                        array('email' => $user->getPrimaryEmail()->getEmail())),
                    'resend_url'      => $person_validator->getResendLink(PersonValidator::TYPE_EMAIL_PRIMARY, $primary_email),
                );
            }
        }

        // comment this out because doctrine entity EmailValidating is empty now
        ////
        //// Extra Email Validation (when adding more emails)
        ////
        //if ($user && $validating_emails = $this->getEmailDataService()->getValidatingEmails($user)) {
        //    foreach ($validating_emails as $validating_email) {
        //        $validation_alerts[] = array(
        //            'type'            => PersonValidator::TYPE_EMAIL,
        //            'message'         => $this->phrase('portal.account.validation_alert_extra_email',
        //                array('email' => $validating_email->getEmail())),
        //            'resend_url'      => $person_validator->getResendLink(
        //                PersonValidator::TYPE_EMAIL,
        //                $validating_email,
        //                null,
        //                true
        //            ),
        //        );
        //    }
        //}

        //
        // DIFFERENT LANG
        //
        $person    = $this->getCurrentPerson();
        $lang_diff = false;
        if (!$person instanceof PersonGuest) {
            // user can click "dismiss" and we store a session var
            if (!$this->getSession()->get('ignore_language_warning', false)) {
                $active_lang = $this->get('language_stack')->getActiveOrDefault();
                if ($person_lang = $person->getLanguage()) {
                    if ($person_lang->getId() != $active_lang->getId()) {
                        $lang_diff = array(
                            'active_lang' => $active_lang,
                            'person_lang' => $person_lang,
                        );
                    }
                }
            }
        }

        //
        // SAVED FORMS
        //
        $saved_forms = array();
        if ($user && $all_saved = $this->getFormSaver()->getSavedForms($user)) {
            foreach ($all_saved as $saved) {
                $saved_forms[] = array(
                    'message' => $this->getFormSaver()->getMessage($saved),
                    'link'    => $this->generateUrl('saved_form_auto_submit', array('auth_code' => $saved->getExternalCode())),
                );
            }
        }

        //
        // TICKETS AWAITING REPLY
        //
        $tickets_awaiting_reply = [];
        if (!$person instanceof PersonGuest) {
            $tickets_awaiting_reply = $this->getRepo('DeskPRO:Ticket')->getWaitingForReplyForPerson($person, 3);
        }

        $should_display = count($saved_forms) || count($validation_alerts) || $lang_diff || count($tickets_awaiting_reply);

        return $this->renderThemeView('Theme:Common:alerts.html.twig', array(
            'user'                   => $user,
            'saved_forms'            => $saved_forms,
            'validation_alerts'      => $validation_alerts,
            'display_alerts'         => $should_display,
            'lang_diff'              => $lang_diff,
            'tickets_awaiting_reply' => $tickets_awaiting_reply,
        ));
    }

    /**
     * DO NOT use always_guest_inline=true on this or we risk cache alert messages to guests.
     *
     * @Tag(name="flashes", esi=true)
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
                'flashes' => $flashes,
            )
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

        return $this->render('Theme:Common:related_content.html.twig', array(
            'content_type'    => $content_type,
            'content_id'      => $content_id,
            'content'         => $content,
            'related_count'   => $count,
            'related_content' => $related_content,
        ));
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
