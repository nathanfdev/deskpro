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

namespace DeskPRO\Bundle\AppBundle\Email;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Templating\Templates\EmailTemplateCode;
use Application\DeskPRO\Templating\Templates\EmailTemplateFile;
use DeskPRO\Bundle\PortalBundle\Person\PersonValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PortalMailer
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function sendPasswordResetLink(Person $person)
    {
        $from = $this->getDefaultOutgoingEmailAddress();

        $context = array(
            'person'                  => $person,
            'reset_url'               => $this->getRouter()->generate(
                'portal_reset_password_process',
                array(
                    'password_reset_code' => $person->getPasswordResetCode(),
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );

        $this->sendMessage(
            'AppBundle:Email:reset-password.html.twig',
            $context,
            $from,
            $person->getPrimaryEmail()->email
        );
    }

    public function sendWelcomeEmail(Person $person)
    {
        $email = $person->getPrimaryEmail();

        if (!$verify_url = $this->getPersonValidator()->getEmailLink(PersonValidator::TYPE_EMAIL, $email)) {
            $verify_url = null;
        }

        $portal_url = $this->getRouter()->generate('portal_index', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        $this->sendToPerson($person, 'DeskPRO:emails_user:register-welcome.html.twig', array(
            'person' => $person,
            'email' => $email,
            'verify_url' => $verify_url,
            'portal_url' => $portal_url
        ));
    }

    public function sendEmailConfirmationEmail(PersonEmail $email, $primary = false)
    {
        $person = $email->getPerson();

        if ($primary) {
            $tpl = 'DeskPRO:emails_user:new-email-validate-primary.html.twig';
            $verify_url = $this->getPersonValidator()->getEmailLink(PersonValidator::TYPE_EMAIL_PRIMARY, $email);
        } else {
            $tpl = 'DeskPRO:emails_user:new-email-validate.html.twig';
            $verify_url = $this->getPersonValidator()->getEmailLink(PersonValidator::TYPE_EMAIL, $email);
        }

        $this->sendToPerson(
            $person,
            $tpl,
            array(
                'person' => $person,
                'email' => $email,
                'verify_url' => $verify_url
            )
        );
    }

    public function sendToPerson(Person $person, $template, $vars)
    {
        $email_template = new EmailTemplateFile($template);
        $message = $this->container->get('mailer')->createMessage();
        $message->setTo($person->getPrimaryEmail()->email, $person->name);
        $message->setTemplate($template, $vars);
        $message->prepare();
        $this->container->get('mailer')->send($message);
    }

    public function sendLoginAlert(Person $person, $success)
    {
        $context = array(
            'success' => $success,
        );

        $from = $this->getDefaultOutgoingEmailAddress();

        $this->sendMessage(
            'AppBundle:Email:login-alert.html.twig',
            $context,
            $from,
            $person->getPrimaryEmail()->email
        );
    }

    public function getDefaultOutgoingEmailAddress()
    {
        $account = App::$container->getEmailAccountManager()->getDefaultOutAccountWithFallback();

        return $account->getUseEmailAddress();
    }

    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context = $this->getTwig()->mergeGlobals($context);

        if (isset($context['person']) && !isset($context['to_name'])) {
            $person = $context['person'];
            $context['to_name'] = $person->name;
        }

        $template = $this->getTwig()->loadTemplate($templateName);
        $subject  = trim($template->renderBlock('subject', $context));
        $textBody = trim($template->renderBlock('text', $context));
        $htmlBody = trim($template->renderBlock('html', $context));
        $message  = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);
        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->getSwiftMailer()->send($message);
    }

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
        if ($brand = $this->container->get('brand_stack')->getActive()) {
            return $brand->getSetting($name, $default);
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
        return $this->container->get('portal_person_validator');
    }
}
