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

namespace DeskPRO\Bundle\PortalBundle\Helper;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\DataService\PersonDataService;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Controller\PasswordController;
use DeskPRO\Bundle\PortalBundle\EmailSender\PortalEmailSender;
use DeskPRO\Bundle\PortalBundle\Model\EmailTo;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PortalValidation
{
    const REGISTRATION = 'registration';
    const ADD_EMAIL    = 'add-email';
    const NEW_FEEDBACK = 'new-feedback';
    const NEW_TICKET   = 'new-ticket';
    const COMMENT      = 'comment';

    public static $types = [
        self::REGISTRATION,
        self::COMMENT,
        self::ADD_EMAIL,
        self::NEW_FEEDBACK,
        self::NEW_TICKET,
    ];

    /**
     * @var PortalEmailSender
     */
    private $mailer;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var PersonDataService
     */
    private $personDataService;

    public function __construct(
        PortalEmailSender $mailer,
        BrandStack $brand_stack,
        UrlGeneratorInterface $url_generator,
        PersonDataService $person_data_service
    ) {
        $this->mailer            = $mailer;
        $this->brandStack        = $brand_stack;
        $this->urlGenerator      = $url_generator;
        $this->personDataService = $person_data_service;
    }

    public function sendTicketVerificationEmail(Ticket $ticket, SavedForm $saved_form)
    {
        // because we need to support an old verify route created with the ticket's "access_code"
        $ticket->forceSetAccessCode($saved_form->getAuthCode());
        $verifyUrl = $this->makeValidationUrl(self::NEW_TICKET, $saved_form);

         // find out who we are emailing to
        if ($person = $saved_form->getPerson()) {
            $emailTo = new EmailTo($person);
        } else {
            if (!$email = $saved_form->getMetaDataValue('email')) {
                throw new \InvalidArgumentException(
                    'trying to send a verification email, but no email provided. saved form must have a Person, or its metadata must have an "email" key.'
                );
            }
            $name    = $saved_form->getMetaDataValue('name');
            $emailTo = new EmailTo();
            $emailTo->setTo($email, $name);
        }

        $this->mailer->sendNewTicketValidationEmail($emailTo, $verifyUrl, $ticket);
    }

    public function sendTicketByEmailVerificationEmail(Person $person, AbstractReader $reader, $authCode)
    {
        $ticket          = new Ticket();
        $ticket->subject = $reader->getSubject()->getSubjectUtf8();
        $ticket->person  = $person;

        $emailTo = new EmailTo($person);

        $verifyUrl = $this->urlGenerator->generate('user_validate_ticketemail', ['auth_code' => $authCode], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->sendNewTicketValidationEmail($emailTo, $verifyUrl, $ticket);
    }

    public function sendVerificationEmail($type, SavedForm $savedForm, $prefer_person_email = true)
    {
        $this->verifyType($type);

        // find out who we are emailing to
        if ($prefer_person_email && $person = $savedForm->getPerson()) {
            $emailTo = new EmailTo($person);
        } else {
            if (!$email = $savedForm->getMetaDataValue('email')) {
                throw new \InvalidArgumentException(
                    'trying to send a verification email, but no email provided. saved form must have a Person, or its metadata must have an "email" key.'
                );
            }
            $name    = $savedForm->getMetaDataValue('name');
            $emailTo = new EmailTo();
            $emailTo->setTo($email, $name);
        }

        // get the verify URL
        $verifyUrl = $this->makeValidationUrl($type, $savedForm, $email);

        switch ($type) {
            case self::REGISTRATION:
            case self::COMMENT:
            case self::NEW_FEEDBACK:
                $this->mailer->sendEmailValidation($emailTo, $verifyUrl);
                break;
            case self::ADD_EMAIL:
                $this->mailer->sendNewEmailValidate($emailTo, $verifyUrl, $savedForm->getPerson());
                break;
            case self::NEW_TICKET:
                throw new \Exception('use sendTicketVerificationEmail instead of sendVerificationEmail for a ticket.');
                break;
        }
    }

    public function sendUsersourceEmailValidation($email, $verify_url)
    {
        $emailTo = new EmailTo();
        $emailTo->setTo($email, $email);
        $this->mailer->sendEmailValidation($emailTo, $verify_url);
    }

    public function getPasswordRedirectIfRequired(Person $person, Request $request, $redirect_to_after_password_set = null)
    {
        // if the guest is a confirmed user that can't login, send them to a page that will let them set a pw
        if (!$person->isUser() && $person->isConfirmed()) {
            // act as if we generated a "set password" token for this user and they clicked the link
            $expireTime    = $this->brandStack->getActive()->getSetting('user.password_reset_code_time_limit', 18000);
            $passwordReset = $this->personDataService->createPasswordReset($person, $expireTime);
            $code          = $passwordReset['code'];

            $session = $request->getSession();
            if ($redirect_to_after_password_set) {
                $session->set(PasswordController::SET_PASSWORD_REDIRECT, $redirect_to_after_password_set);
            }

            if (!$session->has('last_username')) {
                $session->set('last_username', $person->getEmailAddress());
            }
            $session->set(PasswordController::SET_PASSWORD_REDIRECT, $redirect_to_after_password_set);

            return new RedirectResponse(
                $this->urlGenerator->generate('portal_set_password_process',
                    [
                        'code'       => $code,
                        'from-saved' => true,
                    ]
                )
            );
        }

        return false;
    }

    /**
     * @param string    $type
     * @param SavedForm $savedForm
     * @param string    $email
     *
     * @return string
     */
    protected function makeValidationUrl($type, SavedForm $savedForm, $email = null)
    {
        return $this->urlGenerator->generate(
            'portal_validation',
            [
                'type'      => $type,
                'auth_code' => $savedForm->getAuthCode(),
                'email'     => $email,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    protected function verifyType($type)
    {
        if (!in_array($type, self::$types)) {
            throw new \InvalidArgumentException(sprintf('types "%s" is not a valid PortalValidation type', $type));
        }
    }
}
