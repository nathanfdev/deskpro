<?php

namespace Application\EmailBundle\SwiftMailer;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Translate\Translate;
use Application\EmailBundle\SwiftMailer\Message\Message;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\SendmailBundle\Sender\EmailSender;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;

/**
 * Class MailerUtils.
 */
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

    /**
     * Constructor.
     *
     * @param Mailer                                       $mailer
     * @param EmailSender                                  $emailSender
     * @param \DeskPRO\Bundle\BrandBundle\Brand\BrandStack $brandStack
     * @param Translate                                    $translator
     */
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
     * @param Message $message
     * @param Person  $person
     *
     * @return mixed
     */
    public function sendWithPersonContext(Message $message, Person $person = null)
    {
        if ($person) {
            $brand = $person->getBrands()->first();
            if (!$brand) {
                $brand = $this->brandStack->getDefaultBrand();
            }

            // wrap in temporary Brand
            return $this->brandStack->pushTemporary($brand, function () use ($person, $message) {
                // wrap in temporary Language
                $this->translator->setTemporaryLanguage($person->getLanguage(), function () use ($message) {
                    $message->prepare();
                });

                return $this->mailer->send($message);
            });
        } else {
            $message->prepare();

            return $this->mailer->send($message);
        }
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
