<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use Application\DeskPRO\Notifications\NewCommentNotification;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\ReCaptchaType;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Orb\Util\Strings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class GuidesController.
 *
 * @Feature("guides")
 */
class GuidesController extends AbstractController
{
    /**
     * @Route("/guides.{_format}", name="portal_guides", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/guides", name="user_guides_home")
     * @Security("is_granted('USE_GUIDES')")
     * @PageHttpCache()
     *
     * @param Request $request
     * @param string  $_format
     *
     * @return Response
     */
    public function indexAction(Request $request, $_format)
    {
        $person = $this->getCurrentPerson();

        $guides = $this->getGuidesDataService()->getGuides($person);

        $guide = array_shift($guides);

        if (!$guide) {
            return $this->redirectToRoute('portal_home');
        }

        $topic = $guide->getActiveTopics()->first();

        if (!$topic) {
            return $this->redirectToRoute('portal_home');
        }

        return $this->redirectToRoute('portal_guides_topic_permalink', ['slug' => $topic->getId(), 'guide_slug' => $guide->getSlug()]);
    }

    /**
     * @Route("/guides/{slug}", name="user_guides")
     * @ParamConverter(name="guide", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('VIEW_GUIDE', guide)")
     * @PageHttpCache()
     *
     * @param Guide $guide
     *
     * @return Response
     */
    public function browseAction(Guide $guide)
    {
        $topic = $guide->getActiveTopics()->first();

        if (!$topic) {
            return $this->redirectToRoute('portal_home');
        }

        return $this->redirectToRoute('portal_guides_topic_permalink', ['slug' => $topic->getId(), 'guide_slug' => $guide->getSlug()]);
    }

    /**
     * @Route("/guides/{slug}/pdf", name="guides_pdf")
     * @ParamConverter(name="guide", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('VIEW_GUIDE', guide)")
     * @PageHttpCache()
     *
     * @param Guide $guide
     *
     * @return StreamedResponse|NotFoundHttpException
     */
    public function guidePdfAction(Guide $guide)
    {
        $filename = DP_DIR.'/attachments/guides/pdf/'.$guide->getSlug().'.pdf';
        if (!file_exists($filename)) {
            return $this->createNotFoundException();
        }
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Length', filesize($filename));
        $response->setCallback(
            function () use ($filename) {
                $fp = fopen($filename, 'r');
                while (!feof($fp)) {
                    echo fread($fp, 1024);
                }
                fclose($fp);
            }
        );

        return $response;
    }

    /**
     * @Route("/guides/{guide_slug}/{slug}", name="portal_guides_topic_view_short")
     * @Route("/guides/{guide_slug}{parents_slug}/{slug}", requirements={"parents_slug" = "(/.+)?"}, name="portal_guides_topic_view")
     * @Route("/guides/topic/{slug}", name="portal_guides_topic_permalink")
     * @ParamConverter(name="topic", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('VIEW_TOPIC', topic)")
     * @PageHttpCache(content="topic")
     *
     * @param Request $request
     * @param Topic   $topic
     * @param string  $guide_slug
     * @param $visitor_id
     * @param string $parents_slug
     *
     * @return Response
     */
    public function viewAction(Request $request, Topic $topic, $guide_slug, $visitor_id, $parents_slug = '')
    {
        $topicParentsSlug = $topic->getParentsSlug();
        if ($parents_slug !== $topicParentsSlug || $guide_slug !== $topic->getGuideSlug()) {
            return $this->redirectToRoute(
                'portal_guides_topic_view',
                [
                    'slug'         => $topic->getSlug(),
                    'parents_slug' => $topicParentsSlug,
                    'guide_slug'   => $topic->getGuideSlug(),
                ]
            );
        }

        if (!$topic->getParent() || $topic->isNoContent()) {
            /** @var Topic $childTopic */
            $childTopic = $topic->getChildren()->first();

            if ($childTopic) {
                return $this->redirectToRoute(
                    'portal_guides_topic_view',
                    [
                        'slug'         => $childTopic->getSlug(),
                        'parents_slug' => $childTopic->getParentsSlug(),
                        'guide_slug'   => $childTopic->getGuideSlug(),
                    ]
                );
            }
        }

        // COMMENT FORM
        $newCommentForm = null;
        $captcha        = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_TOPIC, $topic)) {
            $formHandler = $this->get('form_handler.comment');
            $comment     = new TopicComment();
            $comment->setVisitorId($visitor_id);
            $comment->setIpAddress($request->getClientIp());
            $newCommentForm = $formHandler->createForm($comment, $request);
            $formResult     = $formHandler->handle($newCommentForm, $request, $topic, $comment);
            if ($formResult) {
                $notify = new NewCommentNotification($comment);
                $notify->send();
            }
            if ($formResult instanceof Response) {
                return $formResult;
            }
            if ($newCommentForm->has('captcha')) {
                $captcha     = $newCommentForm->get('captcha');
                $captchaView = $newCommentForm->get('captcha')->createView();
                $reCaptcha   = $this->getBrandSetting('core.use_recaptcha2')
                    || ReCaptchaType::isCloudRecapchaEnabled();
                if ($reCaptcha) {
                    $siteKey = $this->getBrandSetting('core.recaptcha2_site_key');
                    $captcha = [
                        'type' => 'recaptcha',
                        'key'  => $siteKey,
                    ];
                } else {
                    $captcha = [
                        'type' => 'gregwar',
                    ];
                }
            }
        }

        $serializer = $this->get('serializer');

        $person = $this->getCurrentPerson();

        $guides = $this->getGuidesDataService()->getGuides($person);

        $topicJson = Strings::escapeForJson($serializer->serialize($topic, 'json', new SideloadSerializationContext()));

        return $this->renderThemeView(
            'Theme:Guides:view.html.twig',
            [
                'topic'            => $topic,
                'topic_json'       => $topicJson,
                'captcha'          => $captcha,
                'guide'            => $topic->getGuide(),
                'guides_json'      => Strings::escapeForJson($serializer->serialize($guides, 'json', new SideloadSerializationContext())),
                'new_comment_form' => $newCommentForm ? $newCommentForm->createView() : null,
            ]
        );
    }

    /**
     * @Route("/guide_doc")
     * @Security("is_granted('USE_GUIDES')")
     *
     * @return Response
     */
    public function markdownDocAction()
    {
        return $this->renderThemeView(
            'Theme:Guides:doc.html.twig'
        );
    }

    /**
     * @Route("/guide_pdf/{slug}", name="guides_pdf_extract")
     * @ParamConverter(name="guide", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('VIEW_GUIDE', guide)")
     * @PageHttpCache()
     *
     * @param Guide $guide
     *
     * @return Response
     */
    public function guideFullAction(Guide $guide)
    {
        return $this->renderThemeView(
            'Theme:Guides:full.html.twig',
            [
                'guide' => $guide,
            ]
        );
    }
}
