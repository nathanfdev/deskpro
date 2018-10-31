<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\EmailSender;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\BrandBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Model\EmailTo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * A service to make sending emails from portal services/controllers easier.
 */
class PortalEmailSender
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function sendPasswordResetLink(Person $person, array $reset)
    {
        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            if ($person->isAgent()) {
                $resetUrl = $this->getRouter()->generate(
                    'agent_login',
                    ['code' => $reset['code']],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } else {
                $resetUrl = $this->getRouter()->generate(
                    'portal_reset_password_process',
                    ['code' => $reset['code']],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }
            $viewModel = $this->container->get('email.user_viewmodel_factory')
                ->createResetPasswordModel($resetUrl);
            $this->container->get('email.email_sender')
                ->send($viewModel, ['to' => $person]);
        } else {
            $this->sendTo(
                new EmailTo($person),
                'DeskPRO:emails_user:reset-password.html.twig',
                [
                    'person' => $person,
                    'code'   => $reset['code'],
                ]
            );
        }
    }

    public function sendPasswordSetLink(Person $person, array $reset)
    {
        $resetUrl = $this->getRouter()->generate(
            'portal_set_password_process',
            ['code' => $reset['code']],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->container->get('email.user_viewmodel_factory')
                ->createSetPasswordModel($resetUrl);
            $this->container->get('email.email_sender')
                ->send($viewModel, ['to' => $person]);
        } else {
            $this->sendTo(
                new EmailTo($person),
                'DeskPRO:emails_user:set-password.html.twig',
                [
                    'person'    => $person,
                    'reset_url' => $resetUrl,
                ]
            );
        }
    }

    public function sendWelcomeEmail(Person $person)
    {
        $email = $person->getPrimaryEmail();

        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->container->get('email.user_viewmodel_factory')
                ->createRegisterWelcomeModel();
            $this->container->get('email.email_sender')
                ->send($viewModel, ['to' => $person]);
        } else {
            $portalUrl = $this->getRouter()->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $this->sendTo(
                new EmailTo($person),
                'DeskPRO:emails_user:register-welcome.html.twig',
                [
                    'person'     => $person,
                    'email'      => $email,
                    'verify_url' => null, // BC - this may be in old templates and it should always be null
                    'portal_url' => $portalUrl,
                ]
            );
        }
    }

    public function sendEmailValidation(EmailTo $emailTo, $verifyUrl)
    {
        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->container->get('email.user_viewmodel_factory')->createEmailValidationModel($verifyUrl);
            $this->container->get('email.email_sender')->send($viewModel, ['to' => $emailTo]);
        } else {
            $this->sendTo(
                $emailTo,
                'DeskPRO:emails_user:email-validation.html.twig',
                [
                    'verify_url' => $verifyUrl,
                    'new_email'  => $emailTo->getEmailAddress(),
                ]
            );
        }
    }

    public function sendNewEmailValidate(EmailTo $emailTo, $verifyUrl, Person $person)
    {
        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->container->get('email.user_viewmodel_factory')
                ->createNewEmailValidateModel($verifyUrl, $person->getEmailAddress(), $emailTo->getEmailAddress());
            $this->container->get('email.email_sender')
                ->send($viewModel, ['to' => $emailTo]);
        } else {
            $this->sendTo(
                $emailTo,
                'DeskPRO:emails_user:new-email-validate.html.twig',
                [
                    'verify_url' => $verifyUrl,
                    'new_email'  => $emailTo->getEmailAddress(),
                    'orig_email' => $person->getEmailAddress(),
                ]
            );
        }
    }

    public function sendNewTicketValidationEmail(EmailTo $emailTo, $verifyUrl, Ticket $ticket)
    {
        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->container->get('email.user_viewmodel_factory')
                ->createTicketNewValidateEmailModel($ticket, $verifyUrl);
            $this->container->get('email.email_sender')
                ->send($viewModel, ['to' => $emailTo]);
        } else {
            $this->sendTo(
                $emailTo,
                'DeskPRO:emails_user:ticket-new-validate-email.html.twig',
                [
                    'verify_url' => $verifyUrl,
                    'ticket'     => $ticket,
                ]
            );
        }
    }

    public function sendNewFeedbackEmail(Feedback $feedback)
    {
        $person = $feedback->getPerson();

        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->container->get('email.user_viewmodel_factory')
                ->createFeedbackNewModel($feedback);
            $this->container->get('email.email_sender')
                ->send($viewModel, ['to' => $person]);
        } else {
            $this->sendTo(
                new EmailTo($person),
                'DeskPRO:emails_user:feedback-new.html.twig',
                [
                    'person'     => $person,
                    'feedback'   => $feedback,
                    'verify_url' => null,
                    'validating' => false,
                ]
            );
        }
    }

    /**
     * Is that used anywhere?
     *
     * @param Ticket $ticket
     */
    public function sendNewTicketGuestThankYou(Ticket $ticket)
    {
        $person = $ticket->getPerson();

        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->container->get('email.user_viewmodel_factory')
                ->createNewTicketGuestModel($ticket);
            $this->container->get('email.email_sender')
                ->send($viewModel, ['to' => $person]);
        } else {
            $this->sendTo(
                new EmailTo($person),
                'DeskPRO:emails_user:new-ticket-guest.html.twig',
                [
                    'ticket_view_url' => $this->getRouter()->generate(
                        'portal_tickets_guest_view',
                        [
                            'auth' => $ticket->auth,
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ]
            );
        }
    }

    public function sendCommentThankYouEmail(CommentAbstract $comment)
    {
        $person = $comment->getPerson();

        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->container->get('email.user_viewmodel_factory')
                ->createCommentNewModel($comment);
            $this->container->get('email.email_sender')
                ->send($viewModel, ['to' => $person]);
        } else {
            /** @var \Application\DeskPRO\Entity\ContentAbstract $content */
            $content = $comment->getObject();

            $contentUrl   = $this->container->get('object_router')->getPortalUrl($content);
            $contentTitle = $content->getTranslatedTitle();

            $this->sendTo(
                new EmailTo($person),
                'DeskPRO:emails_user:comment-new.html.twig',
                [
                    'content_url'   => $contentUrl,
                    'content_title' => $contentTitle,
                    'comment'       => $comment, // keep for bc ({{ comment.object.permalink }})
                    'validating'    => false, // keep here for BC
                ]
            );
        }
    }

    public function sendLoginAlert(Person $person, Request $request, $success)
    {
        $created = new \DateTime('@'.$request->getSession()->getMetadataBag()->getCreated());
        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->container->get('email.user_viewmodel_factory')
                ->createLoginAlertModel(
                    $request,
                    $created,
                    $success
                );
            $this->container->get('email.email_sender')
                ->send($viewModel, ['to' => $person]);
        } else {
            $this->sendTo(
                new EmailTo($person),
                'DeskPRO:emails_user:login-alert.html.twig',
                [
                    'request'   => $request,
                    'firstSeen' => $created,
                    'success'   => $success,
                ]
            );
        }
    }

    public function sendTo(EmailTo $emailTo, $template, $vars)
    {
        $vars['site_name'] = $this->getSetting('site_name') ?: $this->getSetting('helpdesk_name');
        /** @var \Application\DeskPRO\Mail\Message $message */
        $message = $this->container->get('mailer')->createMessage();
        if ($person = $emailTo->getPerson()) {
            $message->setToPerson($person);
        } else {
            $message->setTo($emailTo->getEmailAddress(), $emailTo->getName());
        }
        $message->setTemplate($template, $vars);
        $message->addFrom($this->getDefaultOutgoingEmailAddress());
        $message->prepare();
        $this->container->get('mailer')->send($message);
    }

    public function sendShareArticle(Article $article, Person $author, $emails, $formData)
    {
        foreach ($emails as $email) {
            if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                if ($email instanceof Person) {
                    $to = $email;
                } else {
                    $to = $email['address'];
                }
                $viewModel = $this->container->get('email.user_viewmodel_factory')
                    ->createShareArticleModel(
                        $article,
                        $author,
                        $formData['message'],
                        $formData['email'],
                        $formData['name']
                    );
                $this->container->get('email.email_sender')
                    ->send($viewModel, ['to' => $to]);
            } else {
                if ($email instanceof Person) {
                    $emailTo = new EmailTo($email);
                } else {
                    $emailTo = new EmailTo();
                    $emailTo->setTo($email['address'], $email['name']);
                }

                $variables = [
                    'author_email' => $author->getEmailAddress(),
                    'author_name'  => $author->getName(),
                    'article'      => $article,
                ];

                $variables = array_merge($variables, $formData);

                $this->sendTo(
                    $emailTo,
                    'DeskPRO:emails_user:share-article.html.twig',
                    $variables
                );
            }
        }
    }

    public function getDefaultOutgoingEmailAddress()
    {
        $activeBrand = App::$container->get('brand_stack')->getActive()->getBrand();
        $account     = App::$container->getEmailAccountManager()->getDefaultOutAccountWithFallback($activeBrand);

        return $account->getUseEmailAddress();
    }

// the email format we are using right now is the same as what is used in DpKernel (<dp:subject> tags)
// but we will be moving towards more twig-based stuff for new portal. This method will be refactored
// to look more like sendToPerson() but use the new syntax. Keeping for reference.
//    protected function sendMessage()
//    {
//        $context = $this->getTwig()->mergeGlobals($context);

//        if (isset($context['person']) && !isset($context['to_name'])) {
//            $person = $context['person'];
//            $context['to_name'] = $person->name;
//        }

//        $template = $this->getTwig()->loadTemplate($templateName);
//        $subject  = trim($template->renderBlock('subject', $context));
//        $textBody = trim($template->renderBlock('text', $context));
//        $htmlBody = trim($template->renderBlock('html', $context));
//        $message  = \Swift_Message::newInstance()
//            ->setSubject($subject)
//            ->setFrom($fromEmail)
//            ->setTo($toEmail);
//        if (!empty($htmlBody)) {
//            $message->setBody($htmlBody, 'text/html')
//                ->addPart($textBody, 'text/plain');
//        } else {
//            $message->setBody($textBody);
//        }

//        $this->getSwiftMailer()->send($message);
//    }

    /**
     * @return \Swift_Mailer
     */
    protected function getSwiftMailer()
    {
        return $this->container->get('mailer');
    }

    /**
     * @return \Twig_Environment
     */
    protected function getTwig()
    {
        return $this->container->get('twig');
    }

    protected function getSetting($name, $default = null)
    {
        // if we have a brand activated, use its settings
        if ($this->container->has('brand_stack')) {
            /** @var BrandContainer $brand */
            if ($brand = $this->container->get('brand_stack')->getActive()) {
                return $brand->getSetting($name, $default);
            }
        }

        return $this->container->get('settings_resolver')->getGlobalSettings()->get($name, $default);
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Routing\PortalRouter
     */
    protected function getRouter()
    {
        return $this->container->get('router');
    }
}
