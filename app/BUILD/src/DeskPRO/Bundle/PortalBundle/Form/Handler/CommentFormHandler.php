<?php

/**
 * DeskPRO.
 */

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
    private $person_factory;

    /**
     * @var FormFactory
     */
    private $form_factory;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    /**
     * @var AntiAbuse
     */
    private $anti_abuse;

    /**
     * @var PortalValidation
     */
    private $portal_validation;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @var ObjectRouter
     */
    private $object_router;

    /**
     * @var PortalPermissionsManager
     */
    private $permissions_manager;

    /**
     * @var ContentSubscriptionsHelper
     */
    private $subscription_helper;

    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var PersonDataService
     */
    private $person_data_service;
    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var PortalEmailSender
     */
    private $email_sender;

    public function __construct(
        FormSaver $saver,
        PortalValidation $portal_validation,
        LanguageManager $language_manager,
        ObjectRouter $object_router,
        UrlGeneratorInterface $url_generator,
        PersonDataService $person_data_service,
        BrandStack $brand_stack,
        PortalPermissionsManager $permissions_manager,
        ContentSubscriptionsHelper $subscription_helper,
        EntityManager $em,
        PersonFactory $person_factory,
        FormFactory $form_factory,
        TokenStorage $token_storage,
        AntiAbuse $anti_abuse,
        PortalEmailSender $email_sender
    ) {
        $this->saver               = $saver;
        $this->em                  = $em;
        $this->person_factory      = $person_factory;
        $this->form_factory        = $form_factory;
        $this->token_storage       = $token_storage;
        $this->anti_abuse          = $anti_abuse;
        $this->portal_validation   = $portal_validation;
        $this->language_manager    = $language_manager;
        $this->object_router       = $object_router;
        $this->permissions_manager = $permissions_manager;
        $this->subscription_helper = $subscription_helper;
        $this->url_generator       = $url_generator;
        $this->person_data_service = $person_data_service;
        $this->brand_stack         = $brand_stack;
        $this->email_sender        = $email_sender;
    }

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

    public function createForm(CommentAbstract $comment, Request $request)
    {
        if (!$comment->getPerson()) {
            $comment->setPerson($this->getUser());
        }

        return $this->form_factory->create(
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

        return new RedirectResponse($this->object_router->getPortalPath($content));
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
            $this->person_factory->checkGuestForValidation($person, $request->attributes->get('saved-form'));

            // this is someone who clicked the validation link and ended up here (no exception thrown).
            // if its a saved form, it appears to be a guest submission, but its not realy.
            // get the person and set them on the comment.
            $person = $this->person_data_service->getPersonForEmail($email->getEmail());
            $comment->setPerson($person);
            $this->acceptComment($content, $comment, $request);

            $destination = $this->object_router->getPortalPath($content);
            if ($redirect = $this->portal_validation->getPasswordRedirectIfRequired($person, $request, $destination)) {
                return $redirect;
            }
        } catch (LoginRequiredException $e) {
            // oops! A login is required. This "guest" cannot post a comment until logged in.
            $person = $e->getPerson();
            $this->informAntiAbuse($person, $request, $content);

            // return the redirect response
            return $this->saver->saveFormForPersonLogin(SavedForm::TYPE_COMMENT, $person, $form, $request);
        } catch (EmailValidationRequiredException $e) {
            $this->informAntiAbuse($person, $request, $content);

            $saved_form = $this->saver->saveForm(
                SavedForm::TYPE_COMMENT,
                $form,
                $request,
                $person->getEmailAddress(),
                $person->getDisplayName()
            );
            $this->portal_validation->sendVerificationEmail(PortalValidation::COMMENT, $saved_form);
            $this->addFlash($request, 'success', 'portal.flashes.guest_content_must_verify');

            return new RedirectResponse($this->object_router->getPortalPath($content));
        }
    }

    public function acceptComment(ContentAbstract $content, CommentAbstract $comment, Request $request = null)
    {
        $person   = $comment->getPerson();
        $perm_bag = $this->permissions_manager->getPermissionsBagForPerson($person);
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

        $this->email_sender->sendCommentThankYouEmail($comment);

        // auto subscribe
        if ($person = $comment->getPerson()) {
            if ($person instanceof Person) {
                if (!$this->subscription_helper->isSubscribedContent($content, $person)) {
                    $this->subscription_helper->subscribeToContent($content, $person);
                    $this->addFlash($request, 'success', 'portal.flashes.article_subscribe');
                }
            }
        }
    }

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

    protected function addFlash(Request $request, $type, $phrase)
    {
        $request->getSession()->getFlashBag()->add($type, $this->phrase($phrase));
    }

    /**
     * @return \Application\DeskPRO\Entity\Person|null
     */
    protected function getUser()
    {
        if (null === $token = $this->token_storage->getToken()) {
            return new PersonGuest();
        }

        if (!is_object($user = $token->getUser())) {
            return new PersonGuest();
        }

        return $user;
    }

    private function informAntiAbuse($person, Request $request, ContentAbstract $content)
    {
        $check = new SubmitCommentAbuseCheck($person, $request->getClientIp());
        $check->setResponse(new RedirectResponse($this->object_router->getPortalPath($content)));
        $this->anti_abuse->check($check);
    }

    private function phrase($name, array $vars = [])
    {
        return $this->language_manager->phrase($name, $vars);
    }
}
