<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Handler;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitCommentAbuseCheck;
use DeskPRO\Bundle\AppBundle\DataService\PersonDataService;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\EmailSender\PortalEmailSender;
use DeskPRO\Bundle\PortalBundle\Helper\ContentSubscriptionsHelper;
use DeskPRO\Bundle\PortalBundle\Helper\PortalValidation;
use DeskPRO\Bundle\PortalBundle\Person\EmailValidationRequiredException;
use DeskPRO\Bundle\PortalBundle\Person\LoginRequiredException;
use DeskPRO\Bundle\PortalBundle\Person\PersonFactory;
use DeskPRO\Bundle\PortalBundle\SavedForm\FormSaver;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class CommentFormHandler.
 */
class CommentFormHandler
{
    /**
     * @var FormSaver
     */
    private $saver;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var AntiAbuse
     */
    private $antiAbuse;

    /**
     * @var PortalValidation
     */
    private $portalValidation;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var ObjectRouter
     */
    private $objectRouter;

    /**
     * @var PortalPermissionsManager
     */
    private $permissionsManager;

    /**
     * @var ContentSubscriptionsHelper
     */
    private $subscriptionsHelper;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var PersonDataService
     */
    private $personDataService;
    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var PortalEmailSender
     */
    private $emailSender;

    /**
     * Constructor.
     *
     * @param FormSaver                  $saver
     * @param PortalValidation           $portalValidation
     * @param LanguageManager            $languageManager
     * @param ObjectRouter               $objectRouter
     * @param UrlGeneratorInterface      $urlGenerator
     * @param PersonDataService          $personDataService
     * @param BrandStack                 $brandStack
     * @param PortalPermissionsManager   $permissionsManager
     * @param ContentSubscriptionsHelper $subscriptionsHelper
     * @param EntityManager              $em
     * @param PersonFactory              $personFactory
     * @param FormFactory                $formFactory
     * @param TokenStorage               $tokenStorage
     * @param AntiAbuse                  $antiAbuse
     * @param PortalEmailSender          $emailSender
     */
    public function __construct(
        FormSaver                  $saver,
        PortalValidation           $portalValidation,
        LanguageManager            $languageManager,
        ObjectRouter               $objectRouter,
        UrlGeneratorInterface      $urlGenerator,
        PersonDataService          $personDataService,
        BrandStack                 $brandStack,
        PortalPermissionsManager   $permissionsManager,
        ContentSubscriptionsHelper $subscriptionsHelper,
        EntityManager              $em,
        PersonFactory              $personFactory,
        FormFactory                $formFactory,
        TokenStorage               $tokenStorage,
        AntiAbuse                  $antiAbuse,
        PortalEmailSender          $emailSender
    ) {
        $this->saver               = $saver;
        $this->em                  = $em;
        $this->personFactory       = $personFactory;
        $this->formFactory         = $formFactory;
        $this->tokenStorage        = $tokenStorage;
        $this->antiAbuse           = $antiAbuse;
        $this->portalValidation    = $portalValidation;
        $this->languageManager     = $languageManager;
        $this->objectRouter        = $objectRouter;
        $this->permissionsManager  = $permissionsManager;
        $this->subscriptionsHelper = $subscriptionsHelper;
        $this->urlGenerator        = $urlGenerator;
        $this->personDataService   = $personDataService;
        $this->brandStack          = $brandStack;
        $this->emailSender         = $emailSender;
    }

    /**
     * @param FormInterface   $form
     * @param Request         $request
     * @param ContentAbstract $content
     * @param CommentAbstract $comment
     *
     * @return bool|RedirectResponse
     */
    public function handle(FormInterface $form, Request $request, ContentAbstract $content, CommentAbstract $comment)
    {
        $comment->setObject($content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $person = $comment->getPerson();
            if ($person instanceof PersonGuest) {
                return $this->handleGuestSubmit($person, $form, $request, $content, $comment);
            }

            return $this->handleLoggedInPersonSubmit($request, $content, $comment, $person);
        }

        return false;
    }

    /**
     * @param CommentAbstract $comment
     * @param Request         $request
     *
     * @return FormInterface
     */
    public function createForm(CommentAbstract $comment, Request $request)
    {
        if (!$comment->getPerson()) {
            $comment->setPerson($this->getUser());
        }

        return $this->formFactory->create(
            'comment',
            $comment,
            [
                'person'                => $this->getUser(),
                'allow_extra_fields'    => true,
                'saved_form_subrequest' => $request->attributes->has('saved-form'),
            ]
        );
    }

    /**
     * @param Request         $request
     * @param ContentAbstract $content
     * @param CommentAbstract $comment
     * @param                 $person
     *
     * @return RedirectResponse
     */
    protected function handleLoggedInPersonSubmit(
        Request $request,
        ContentAbstract $content,
        CommentAbstract $comment,
        Person $person
    ) {
        $this->informAntiAbuse($person, $request, $content);
        $this->acceptComment($content, $comment, $request);

        return new RedirectResponse($this->objectRouter->getPortalPath($content));
    }

    /**
     * @param PersonGuest     $person
     * @param FormInterface   $form
     * @param Request         $request
     * @param ContentAbstract $content
     * @param CommentAbstract $comment
     *
     * @return RedirectResponse
     */
    protected function handleGuestSubmit(
        PersonGuest $person,
        FormInterface $form,
        Request $request,
        ContentAbstract $content,
        CommentAbstract $comment
    ) {
        // we are dealing with a guest...
        // find if this is already a person
        // if it is, use the auto login feature to submit
        // if it isn't, save the form and send a validation link

        // comment forms dont put data on the PersonGuest, so we take it from the comment itself:
        $email        = new PersonEmail();
        $email->email = $comment->getEmail();
        $person->setPrimaryEmail($email);
        $person->setName($comment->getName());
        try {
            $this->personFactory->checkGuestForValidation($person, $request->attributes->get('saved-form'));

            // this is someone who clicked the validation link and ended up here (no exception thrown).
            // if its a saved form, it appears to be a guest submission, but its not realy.
            // get the person and set them on the comment.
            $person = $this->personDataService->getPersonForEmail($email->getEmail());
            $comment->setPerson($person);
            $this->acceptComment($content, $comment, $request);

            $destination = $this->objectRouter->getPortalPath($content);
            if ($redirect = $this->portalValidation->getPasswordRedirectIfRequired($person, $request, $destination)) {
                return $redirect;
            }
        } catch (LoginRequiredException $e) {
            // oops! A login is required. This "guest" cannot post a comment until logged in.
            $person = $e->getPerson();
            $this->informAntiAbuse($person, $request, $content);

            // return the redirect response
            if ($person instanceof PersonGuest) {
                $savedForm = $this->saver->saveForm(SavedForm::TYPE_COMMENT, $form, $request, $person->getEmail(), $person->getDisplayName());

                return new RedirectResponse(
                    $this->urlGenerator->generate('portal_login', [
                        'saved_form' => $savedForm->getExternalCode(),
                    ])
                );
            } else {
                return $this->saver->saveFormForPersonLogin(SavedForm::TYPE_COMMENT, $person, $form, $request);
            }
        } catch (EmailValidationRequiredException $e) {
            $this->informAntiAbuse($person, $request, $content);

            $saved_form = $this->saver->saveForm(
                SavedForm::TYPE_COMMENT,
                $form,
                $request,
                $person->getEmailAddress(),
                $person->getDisplayName()
            );
            $this->portalValidation->sendVerificationEmail(PortalValidation::COMMENT, $saved_form);
            $this->addFlash($request, 'success', 'portal.flashes.guest_content_must_verify');

            return new RedirectResponse($this->objectRouter->getPortalPath($content));
        }
    }

    /**
     * @param ContentAbstract $content
     * @param CommentAbstract $comment
     * @param Request|null    $request
     */
    public function acceptComment(ContentAbstract $content, CommentAbstract $comment, Request $request = null)
    {
        $person   = $comment->getPerson();
        $perm_bag = $this->permissionsManager->getPermissionsBagForPerson($person);
        if (!$perm_bag->get($this->permPrefix($content).'.no_comment_validate')
            && !($person->isAgent() && $person->hasPerm('agent_publish.validate'))
        ) {
            // hide the comment until its approved
            $comment->setStatus(CommentAbstract::STATUS_HIDDEN);
            $this->addFlash($request, 'success', 'portal.flashes.comment_thank_you_review');
        } else {
            $comment->setStatus(CommentAbstract::STATUS_VISIBLE);
            $this->addFlash($request, 'success', 'portal.flashes.comment_thank_you');
        }
        $content->addComment($comment);
        $this->em->persist($comment);
        $this->em->flush([$comment, $content]);

        $this->emailSender->sendCommentThankYouEmail($comment);

        // auto subscribe
        if ($person = $comment->getPerson()) {
            if ($person instanceof Person) {
                if (!$this->subscriptionsHelper->isSubscribedContent($content, $person)) {
                    $this->subscriptionsHelper->subscribeToContent($content, $person);
                    $this->addFlash($request, 'success', 'portal.flashes.article_subscribe');
                }
            }
        }
    }

    /**
     * @param ContentAbstract $content
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function permPrefix(ContentAbstract $content)
    {
        if ($content instanceof Article) {
            return 'articles';
        } elseif ($content instanceof Download) {
            return 'downloads';
        } elseif ($content instanceof News) {
            return 'news';
        } elseif ($content instanceof Feedback) {
            return 'feedback';
        } elseif ($content instanceof Topic) {
            return 'topic';
        }

        throw new InvalidArgumentException('content type not supported');
    }

    /**
     * @param Request $request
     * @param string  $type
     * @param string  $phrase
     */
    protected function addFlash(Request $request, $type, $phrase)
    {
        $request->getSession()->getFlashBag()->add($type, $this->phrase($phrase));
    }

    /**
     * @return \Application\DeskPRO\Entity\Person|null
     */
    protected function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return new PersonGuest();
        }

        if (!is_object($user = $token->getUser())) {
            return new PersonGuest();
        }

        return $user;
    }

    /**
     * @param mixed           $person
     * @param Request         $request
     * @param ContentAbstract $content
     */
    private function informAntiAbuse($person, Request $request, ContentAbstract $content)
    {
        $check = new SubmitCommentAbuseCheck($person, $request->getClientIp());
        $check->setResponse(new RedirectResponse($this->objectRouter->getPortalPath($content)));
        $this->antiAbuse->check($check);
    }

    /**
     * @param string $name
     * @param array  $vars
     *
     * @return string
     */
    private function phrase($name, array $vars = [])
    {
        return $this->languageManager->phrase($name, $vars);
    }
}
