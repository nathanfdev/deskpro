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
namespace DeskPRO\Bundle\PortalBundle\EmailSender;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\PortalBundle\Model\EmailTo;
use DeskPRO\Bundle\PortalBundle\Person\PersonValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PortalEmailSender
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function sendPasswordResetLink(Person $person, array $reset)
    {
        $this->sendTo(
            new EmailTo($person),
            'EmailBundle:Portal:reset-password.html.twig',
            array(
                'person'    => $person,
                'reset_url' => $this->getRouter()->generate(
                    'portal_reset_password_process',
                    array(
                        'code' => $reset['code'],
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            )
        );
    }

    public function sendPasswordSetLink(Person $person, array $reset)
    {
        $this->sendTo(
            new EmailTo($person),
            'EmailBundle:Portal:set-password.html.twig',
            array(
                'person'    => $person,
                'reset_url' => $this->getRouter()->generate(
                    'portal_set_password_process',
                    array(
                        'code' => $reset['code'],
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            )
        );
    }

    public function sendWelcomeEmail(Person $person)
    {
        $email = $person->getPrimaryEmail();

        $portal_url = $this->getRouter()->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->sendTo(
            new EmailTo($person),
            'DeskPRO:emails_user:register-welcome.html.twig',
            array(
                'person'     => $person,
                'email'      => $email,
                'verify_url' => null, // BC - this may be in old templates and it should always be null
                'portal_url' => $portal_url,
            )
        );
    }

    public function sendEmailValidation(EmailTo $email_to, $verify_url)
    {
        $this->sendTo(
            $email_to,
            'EmailBundle:Portal:email-validation.html.twig',
            [
                'verify_url' => $verify_url,
            ]
        );
    }

    public function sendFeedbackValidationLink(Feedback $feedback)
    {
        $person = $feedback->getPerson();

        $tpl        = 'DeskPRO:emails_user:feedback-new.html.twig';
        $verify_url = $this->getPersonValidator()->getEmailLink(PersonValidator::TYPE_FEEDBACK, $person->getPrimaryEmail(), $feedback->getId());

        $this->sendTo(
            new EmailTo($person),
            $tpl,
            array(
                'person'     => $person,
                'verify_url' => $verify_url,
                'feedback'   => $feedback,
                'validating' => $person->isUserValid() ? null : 'new',
            )
        );
    }

    public function sendNewTicketGuestThankYou(Ticket $ticket)
    {
        $person = $ticket->getPerson();

        $this->sendTo(
            new EmailTo($person),
            'EmailBundle:Portal:new-ticket-guest.html.twig',
            array(
                'ticket_view_url' => $this->getRouter()->generate(
                    'portal_tickets_guest_view',
                    array(
                        'auth' => $ticket->auth,
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            )
        );
    }

    public function sendLoginAlert(Person $person, $success)
    {
        $this->sendTo(
            new EmailTo($person),
            'EmailBundle:Portal:login-alert.html.twig',
            array(
                'success' => $success,
            )
        );
    }

    public function sendTo(EmailTo $email_to, $template, $vars)
    {
        /** @var \Application\DeskPRO\Mail\Message $message */
        $message = $this->container->get('mailer')->createMessage();
        if ($person = $email_to->getPerson()) {
            $message->setToPerson($person);
        } else {
            $message->setTo($email_to->getEmailAddress(), $email_to->getName());
        }
        $message->setTemplate($template, $vars);
        $message->prepare();
        $this->container->get('mailer')->send($message);
    }

    public function getDefaultOutgoingEmailAddress()
    {
        $account = App::$container->getEmailAccountManager()->getDefaultOutAccountWithFallback();

        return $account->getUseEmailAddress();
    }

// the email format we are using right now is the same as what is used in DpKernel (<dp:subject> tags)
// but we will be moving towards more twig-based stuff for new portal. This method will be refactored
// to look more like sendToPerson() but use the new syntax. Keeping for reference.
//    protected function sendMessage()
//    {
//        $context = $this->getTwig()->mergeGlobals($context);
//
//        if (isset($context['person']) && !isset($context['to_name'])) {
//            $person = $context['person'];
//            $context['to_name'] = $person->name;
//        }
//
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
//
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

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Person\PersonValidator
     */
    protected function getPersonValidator()
    {
        return $this->container->get('person.portal_validator');
    }
}
