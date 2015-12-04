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

namespace DeskPRO\Bundle\PortalBundle\Helper;

use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\EmailSender\PortalEmailSender;
use DeskPRO\Bundle\PortalBundle\Model\EmailTo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PortalValidation
{
    const REGISTRATION = 'registration';
    const COMMENT      = 'comment';

    public static $types = [
      self::REGISTRATION,
      self::COMMENT,
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

    public function __construct(PortalEmailSender $mailer, BrandStack $brand_stack, UrlGeneratorInterface $url_generator)
    {
        $this->mailer        = $mailer;
        $this->brand_stack   = $brand_stack;
        $this->url_generator = $url_generator;
    }

    public function sendVerificationEmail($type, SavedForm $saved_form)
    {
        $this->verifyType($type);

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

        // get the verify URL
        $verify_url = $this->makeValidationUrl($type, $saved_form);

        switch ($type) {
            case self::REGISTRATION:
                $this->mailer->sendEmailValidation($email_to, $verify_url);
                break;
            case self::COMMENT:
                $this->mailer->sendEmailValidation($email_to, $verify_url);
                break;
        }

        // based on $type, create the correct verification url
        // based on $type, send the right email
    }

    public function processVerificationClick($type, SavedForm $saved_form)
    {
        $this->verifyType($type);
        // someone clicked the link
        // based on $type, actions need to take place
        // and a redirection must be returned
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
