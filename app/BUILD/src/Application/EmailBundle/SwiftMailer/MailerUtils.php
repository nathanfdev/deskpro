<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SwiftMailer;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Translate\Translate;
use Application\EmailBundle\SwiftMailer\Message\Message;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\SendmailBundle\Sender\EmailSender;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;

class MailerUtils
{
    /**
     * @var BrandStack
     */
    protected $brandStack;

    /**
     * @var Translate
     */
    protected $translator;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var EmailSender
     */
    protected $emailSender;

    public function __construct(Mailer $mailer, EmailSender $emailSender, BrandStack $brandStack, Translate $translator)
    {
        $this->mailer      = $mailer;
        $this->emailSender = $emailSender;
        $this->brandStack  = $brandStack;
        $this->translator  = $translator;
    }

    /**
     * Setup BrandStack and Translator in $person context and then compose/send message.
     *
     * This will attempt to catch exceptions so the brand is always reset afterwards.
     *
     * @param Person  $person
     * @param Message $message
     *
     * @return mixed
     */
    public function sendWithPersonContext(Person $person, Message $message)
    {
        $brand = $person->getBrands()->first();
        if (!$brand) {
            $brand = $this->brandStack->getDefaultBrand();
        }

        $self = $this;

        // wrap in temporary Brand
        return $this->brandStack->pushTemporary($brand, function () use ($self, $person, $message) {
            // wrap in temporary Language
            $self->translator->setTemporaryLanguage($person->getLanguage(), function () use ($message) {
                $message->prepare();
            });

            return $self->mailer->send($message);
        });
    }

    /**
     * This function for new email templates feature
     * Setup BrandStack  in $person context and then compose/send message.
     *
     * This will attempt to catch exceptions so the brand is always reset afterwards.
     *
     *
     * @param Person  $person
     * @param Message $message
     *
     * @return mixed
     */
    public function sendModelWithPersonContext(Person $person, EmailBaseType $model, $args)
    {
        $res = null;

        // we don't need to wrap into $translator->setTemporaryLanguage, because it is done in SendMail directly
        $self = $this;
        $this->brandStack->pushTemporary(
            $person->getBrands()->first(),
            function () use ($self, $model, $args, &$res) {
                $res = $self->emailSender->send($model, $args);
            }
        );

        return $res;
    }
}
