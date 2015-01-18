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

namespace Application\AppBundle\Mailer;


use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewMailer
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
            'person' => $person,
            'reset_url' => $this->getRouter()->generate('portal_reset_password_process', array(
                'password_reset_code' => $person->getPasswordResetCode()
            ))
    );

        $this->sendMessage(
            'AppBundle:Mail:reset-password.html.twig',
            $context,
            $from,
            $person->getPrimaryEmail()->email
        );
    }

    public function sendLoginAlert(Person $person, $success)
    {
        $context = array(
            'success' => $success
        );

        $from = $this->getDefaultOutgoingEmailAddress();

        $this->sendMessage(
            'AppBundle:Mail:login-alert.html.twig',
            $context,
            $from,
            $person->getPrimaryEmail()->email
        );
    }

    /**
     * @param string $templateName
     * @param array $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context = $this->getTwig()->mergeGlobals($context);

        $template = $this->getTwig()->loadTemplate($templateName);
        $subject = trim($template->renderBlock('subject', $context));
        $textBody = trim($template->renderBlock('text', $context));
        $htmlBody = trim($template->renderBlock('html', $context));
        $message = \Swift_Message::newInstance()
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
    public function getSwiftMailer()
    {
        return $this->container->get('mailer');
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->container->get('twig');
    }

    /**
     * @return \Application\LanguageBundle\Routing\Router
     */
    public function getRouter()
    {
        return $this->container->get('router');
    }

    public function getDefaultOutgoingEmailAddress()
    {
        $account = App::$container->getEmailAccountManager()->getDefaultOutAccountWithFallback();

        return $account->getUseEmailAddress();
    }
}