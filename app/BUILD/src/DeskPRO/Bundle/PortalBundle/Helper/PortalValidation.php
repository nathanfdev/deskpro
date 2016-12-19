<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
    private $brand_stack;

    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var PersonDataService
     */
    private $person_data_service;

    public function __construct(
        PortalEmailSender $mailer,
        BrandStack $brand_stack,
        UrlGeneratorInterface $url_generator,
        PersonDataService $person_data_service
    ) {
        $this->mailer              = $mailer;
        $this->brand_stack         = $brand_stack;
        $this->url_generator       = $url_generator;
        $this->person_data_service = $person_data_service;
    }

    public function sendTicketVerificationEmail(Ticket $ticket, SavedForm $saved_form)
    {
        // because we need to support an old verify route created with the ticket's "access_code"
        $ticket->forceSetAccessCode($saved_form->getAuthCode());
        $verify_url = $this->makeValidationUrl(self::NEW_TICKET, $saved_form);

         // find out who we are emailing to
        if ($person = $saved_form->getPerson()) {
            $email_to = new EmailTo($person);
        } else {
            if (!$email = $saved_form->getMetaDataValue('email')) {
                throw new \InvalidArgumentException(
                    'trying to send a verification email, but no email provided. saved form must have a Person, or its metadata must have an "email" key.'
                );
            }
            $name     = $saved_form->getMetaDataValue('name');
            $email_to = new EmailTo();
            $email_to->setTo($email, $name);
        }

        $this->mailer->sendNewTicketValidationEmail($email_to, $verify_url, $ticket);
    }

    public function sendTicketByEmailVerificationEmail(Person $person, AbstractReader $reader, $authcode)
    {
        $ticket          = new Ticket();
        $ticket->subject = $reader->getSubject()->getSubjectUtf8();
        $ticket->person  = $person;

        $email_to = new EmailTo($person);

        $verify_url = $this->url_generator->generate('user_validate_ticketemail', ['auth_code' => $authcode], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->sendNewTicketValidationEmail($email_to, $verify_url, $ticket);
    }

    public function sendVerificationEmail($type, SavedForm $saved_form, $prefer_person_email = true)
    {
        $this->verifyType($type);

        // find out who we are emailing to
        if ($prefer_person_email && $person = $saved_form->getPerson()) {
            $email_to = new EmailTo($person);
        } else {
            if (!$email = $saved_form->getMetaDataValue('email')) {
                throw new \InvalidArgumentException(
                    'trying to send a verification email, but no email provided. saved form must have a Person, or its metadata must have an "email" key.'
                );
            }
            $name     = $saved_form->getMetaDataValue('name');
            $email_to = new EmailTo();
            $email_to->setTo($email, $name);
        }

        // get the verify URL
        $verify_url = $this->makeValidationUrl($type, $saved_form);

        switch ($type) {
            case self::REGISTRATION:
            case self::COMMENT:
            case self::NEW_FEEDBACK:
                $this->mailer->sendEmailValidation($email_to, $verify_url);
                break;
            case self::ADD_EMAIL:
                $this->mailer->sendNewEmailValidate($email_to, $verify_url, $saved_form->getPerson());
                break;
            case self::NEW_TICKET:
                throw new \Exception('use sendTicketVerificationEmail instead of sendVerificationEmail for a ticket.');
                break;
        }
    }

    public function sendUsersourceEmailValidation($email, $verify_url)
    {
        $email_to = new EmailTo();
        $email_to->setTo($email, $email);
        $this->mailer->sendEmailValidation($email_to, $verify_url);
    }

    public function getPasswordRedirectIfRequired(Person $person, Request $request, $redirect_to_after_password_set = null)
    {
        // if the guest is a confirmed user that can't login, send them to a page that will let them set a pw
        if (!$person->isUser() && $person->isConfirmed()) {
            // act as if we generated a "set password" token for this user and they clicked the link
            $expire_time    = $this->brand_stack->getActive()->getSetting('user.password_reset_code_time_limit', 18000);
            $password_reset = $this->person_data_service->createPasswordReset($person, $expire_time);
            $code           = $password_reset['code'];

            $session = $request->getSession();
            if ($redirect_to_after_password_set) {
                $session->set(PasswordController::SET_PASSWORD_REDIRECT, $redirect_to_after_password_set);
            }

            if (!$session->has('last_username')) {
                $session->set('last_username', $person->getEmailAddress());
            }
            $session->set(PasswordController::SET_PASSWORD_REDIRECT, $redirect_to_after_password_set);

            return new RedirectResponse(
                $this->url_generator->generate('portal_set_password_process',
                    [
                        'code'       => $code,
                        'from-saved' => true,
                    ]
                )
            );
        }
    }

    protected function makeValidationUrl($type, SavedForm $saved_form)
    {
        return $this->url_generator->generate(
            'portal_validation',
            [
                'type'      => $type,
                'auth_code' => $saved_form->getAuthCode(),
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
